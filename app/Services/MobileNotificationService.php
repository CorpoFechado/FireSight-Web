<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\Notification;
use App\Models\User;

class MobileNotificationService
{
    public function __construct(
        protected GeoBoundaryService $geoService,
    ) {}

    /**
     * Creates notifications when BFP personnel updates report status:
     * 1. Notifies the resident reporter.
     * 2. Notifies other active BFP personnel (excluding the actor).
     * (Admin notification is handled automatically by CommunityReportObserver + AdminNotifier).
     */
    public function notifyReportStatusChange(int $reportId, string $status, ?int $actorUserId = null): void
    {
        $report = CommunityReport::with('barangay')->find($reportId);
        if (! $report) {
            return;
        }

        $statusLabels = [
            CommunityReport::STATUS_PENDING => 'Pending',
            CommunityReport::STATUS_ACCEPTED => 'Accepted',
            CommunityReport::STATUS_DISPATCHED => 'Dispatched',
            CommunityReport::STATUS_RESOLVED => 'Resolved',
            CommunityReport::STATUS_INVALID => 'Invalid',
        ];

        $label = $statusLabels[$status] ?? ucfirst($status);

        // 1. Notify Resident Reporter
        if ($report->user_id) {
            $residentMessages = [
                'accepted' => "Your fire report (#{$reportId}) has been accepted by BFP personnel.",
                'dispatched' => "BFP personnel have been dispatched for your fire report (#{$reportId}).",
                'resolved' => "Your fire report (#{$reportId}) has been marked as resolved by BFP personnel.",
                'invalid' => "Your fire report (#{$reportId}) has been marked as false or invalid by BFP personnel.",
            ];

            $residentMessage = $residentMessages[$status] ?? "The status of your fire report (#{$reportId}) has been updated to: {$status}.";

            Notification::create([
                'user_id' => $report->user_id,
                'report_id' => $reportId,
                'title' => 'Report Status Updated',
                'message' => $residentMessage,
                'notification_type' => 'status_update',
                'is_read' => false,
            ]);
        }

        // 2. Notify other active BFP Personnel on mobile
        $actor = $actorUserId ? User::with('personnelDetails')->find($actorUserId) : null;
        $actorName = 'BFP personnel';
        if ($actor) {
            $rank = $actor->personnelDetails?->rank;
            $actorName = trim(($rank ? "{$rank} " : '').$actor->name) ?: 'BFP personnel';
        }

        $barangaySuffix = $report->barangay?->barangay_name ? " in {$report->barangay->barangay_name}" : '';
        $personnelTitle = $status === CommunityReport::STATUS_INVALID ? 'Report Marked Invalid' : "Report {$label}";
        $personnelMessage = "{$actorName} marked Report #{$reportId}{$barangaySuffix} as {$label}.";

        $personnelIds = User::query()
            ->where('role', User::ROLE_BFP_PERSONNEL)
            ->where('status', User::STATUS_ACTIVE)
            ->when($actorUserId !== null, fn ($query) => $query->whereKeyNot($actorUserId))
            ->pluck('user_id');

        if ($personnelIds->isNotEmpty()) {
            Notification::insert($personnelIds->map(fn (int $id) => [
                'user_id' => $id,
                'report_id' => $reportId,
                'title' => $personnelTitle,
                'message' => $personnelMessage,
                'notification_type' => 'status_update',
                'is_read' => false,
                'created_at' => now(),
            ])->all());
        }
    }

    /**
     * Creates a confirmation notification for the resident when their report is submitted.
     */
    public function notifyResidentReportSubmitted(int $userId, ?int $reportId = null): void
    {
        $message = $reportId
            ? "Your fire report (#{$reportId}) has been received and is under review by BFP Lian."
            : 'Your fire report has been received and is under review by BFP Lian.';

        Notification::create([
            'user_id' => $userId,
            'report_id' => $reportId,
            'title' => 'Report Submitted',
            'message' => $message,
            'notification_type' => 'incident_alert',
            'is_read' => false,
        ]);
    }

    /**
     * Creates a notification for every active BFP personnel account.
     * (Admin accounts are excluded here since AdminNotifier via Observer handles Admins).
     */
    public function notifyPersonnelOfNewReport(int $reportId, ?string $submittedLocation = null): void
    {
        $report = CommunityReport::with('barangay')->find($reportId);
        if (! $report) {
            return;
        }

        // Only target BFP Personnel (Admin is alerted by CommunityReportObserver + AdminNotifier)
        $personnelIds = User::where('role', User::ROLE_BFP_PERSONNEL)
            ->where('status', User::STATUS_ACTIVE)
            ->pluck('user_id');

        if ($personnelIds->isEmpty()) {
            return;
        }

        $locationName = trim((string) $submittedLocation);
        if ($locationName === '' && $report->barangay) {
            $locationName = trim((string) $report->barangay->barangay_name);
        }

        if ($locationName === '' && $report->latitude !== null && $report->longitude !== null) {
            $matchedBarangayId = $this->geoService->findBarangayByLocation((float) $report->latitude, (float) $report->longitude);
            if ($matchedBarangayId !== null) {
                $b = Barangay::find($matchedBarangayId);
                if ($b) {
                    $locationName = trim($b->barangay_name);
                }
            }
        }

        if ($locationName === '' && $report->latitude !== null && $report->longitude !== null) {
            $locationName = $this->geoService->findReadableLocation(
                (float) $report->latitude,
                (float) $report->longitude,
                $locationName !== '' ? $locationName : null
            );
        }

        if ($locationName === '') {
            $locationName = 'submitted location';
        }

        $title = "Fire Report near {$locationName}";

        $label = $report->ai_fire_label;
        $confidence = $report->ai_fire_confidence !== null ? (float) $report->ai_fire_confidence : null;

        if ($label === 'fire') {
            $aiSummary = $confidence !== null
                ? sprintf('The image assessment indicates that fire was detected with %.1f%% confidence.', $confidence * 100)
                : 'The image assessment indicates that fire was detected.';
        } elseif ($label === 'non_fire') {
            $aiSummary = $confidence !== null
                ? sprintf('The image assessment indicates that no fire was detected with %.1f%% confidence.', $confidence * 100)
                : 'The image assessment indicates that no fire was detected.';
        } else {
            $aiSummary = 'The image assessment is currently unavailable.';
        }

        $message = "A new fire report (#{$reportId}) was submitted near {$locationName}. {$aiSummary}";

        foreach ($personnelIds as $userId) {
            // Deduplicate: avoid sending multiple notifications for the exact same report ID
            $alreadyExists = Notification::where('user_id', $userId)
                ->where('notification_type', 'incident_alert')
                ->where('report_id', $reportId)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            Notification::create([
                'user_id' => $userId,
                'report_id' => $reportId,
                'title' => $title,
                'message' => $message,
                'notification_type' => 'incident_alert',
                'is_read' => false,
            ]);
        }
    }
}
