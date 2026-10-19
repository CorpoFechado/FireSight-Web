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
     * Creates a resident notification for a community report status change.
     * Reports without a resident owner, such as manual BFP entries, are skipped.
     */
    public function notifyReportStatusChange(int $reportId, string $status): void
    {
        $report = CommunityReport::find($reportId);
        if (! $report || ! $report->user_id) {
            return;
        }

        // Map each status to a complete, natural-sounding sentence matching firesight-api
        $messages = [
            'accepted' => 'Your fire report has been accepted by BFP personnel.',
            'dispatched' => 'BFP personnel have been dispatched to your reported location.',
            'resolved' => 'Your fire report has been marked as resolved by BFP personnel.',
            'invalid' => 'Your fire report has been marked as false or invalid by BFP personnel.',
        ];

        $message = $messages[$status] ?? "The status of your fire report has been updated to: {$status}.";

        Notification::create([
            'user_id' => $report->user_id,
            'title' => 'Report Status Updated',
            'message' => $message,
            'notification_type' => 'status_update',
            'is_read' => false,
        ]);
    }

    /**
     * Creates a confirmation notification for the resident when their report is submitted.
     */
    public function notifyResidentReportSubmitted(int $userId): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => 'Report Submitted',
            'message' => 'Your fire report has been received and is under review by BFP Lian.',
            'notification_type' => 'incident_alert',
            'is_read' => false,
        ]);
    }

    /**
     * Creates a notification for every active BFP personnel and admin account.
     */
    public function notifyPersonnelOfNewReport(int $reportId, ?string $submittedLocation = null): void
    {
        $report = CommunityReport::with('barangay')->find($reportId);
        if (! $report) {
            return;
        }

        $personnelIds = User::whereIn('role', [User::ROLE_BFP_PERSONNEL, User::ROLE_BFP_ADMIN])
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
                ->where('message', 'like', "%#{$reportId}%")
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'notification_type' => 'incident_alert',
                'is_read' => false,
            ]);
        }
    }
}
