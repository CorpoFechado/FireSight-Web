<?php

namespace App\Support;

use App\Models\CommunityReport;
use App\Models\Notification;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Builds and fans out staff-facing notifications for fire incident activity.
 */
class AdminNotifier
{
    /** Reports within this radius of an active report are flagged as possible duplicates. */
    public const DUPLICATE_RADIUS_METERS = 300;

    /** Only active reports submitted within this many hours are considered for duplicates. */
    public const DUPLICATE_WINDOW_HOURS = 3;

    /** @var array<string, string> */
    private const STATUS_LABELS = [
        CommunityReport::STATUS_PENDING => 'Pending',
        CommunityReport::STATUS_ACCEPTED => 'Accepted',
        CommunityReport::STATUS_DISPATCHED => 'Dispatched',
        CommunityReport::STATUS_RESOLVED => 'Resolved',
        CommunityReport::STATUS_INVALID => 'Invalid',
    ];

    /**
     * A new community report came in. Notifies active BFP admins.
     * (BFP personnel and resident confirmation are notified directly by the mobile submission endpoint).
     */
    public function reportSubmitted(CommunityReport $report, ?CarbonInterface $at = null): void
    {
        $this->sendToRoles(
            'New Community Report',
            "Report #{$report->report_id} from {$this->reporterName($report)}{$this->locationSuffix($report)} is awaiting verification.",
            'incident_alert',
            [User::ROLE_BFP_ADMIN],
            null,
            $at,
            (int) $report->report_id,
        );
    }

    /**
     * @param  Collection<int, CommunityReport>  $nearbyReports
     */
    public function possibleDuplicate(CommunityReport $report, Collection $nearbyReports, ?CarbonInterface $at = null): void
    {
        if ($nearbyReports->isEmpty()) {
            return;
        }

        $reportList = $nearbyReports
            ->map(fn (CommunityReport $nearby) => "#{$nearby->report_id}")
            ->implode(', ');
        $noun = $nearbyReports->count() === 1 ? 'Report' : 'Reports';

        $this->sendToRoles(
            'Possible Duplicate Report',
            "Report #{$report->report_id} was submitted near active {$noun} {$reportList}. Review and link them if they describe the same incident.",
            'incident_alert',
            [User::ROLE_BFP_ADMIN],
            null,
            $at,
            (int) $report->report_id,
        );
    }

    /**
     * Handles status changes triggered anywhere in the system.
     * - If triggered by Admin (Web Portal) or System/Seeder:
     *   Notifies other Admins, active BFP Personnel, and the Resident reporter.
     * - If triggered by Personnel (Mobile App):
     *   Notifies active Admins (Mobile API notifies other personnel and resident).
     */
    public function statusChanged(CommunityReport $report, string $status, ?User $actor, ?CarbonInterface $at = null): void
    {
        $label = self::STATUS_LABELS[$status] ?? ucfirst($status);
        $title = $status === CommunityReport::STATUS_INVALID ? 'Report Marked Invalid' : "Report {$label}";
        $staffMessage = "{$this->actorName($actor)} marked Report #{$report->report_id}{$this->locationSuffix($report)} as {$label}.";
        $reportId = (int) $report->report_id;
        $actorId = $actor?->getKey();

        // 1. Notify BFP Admins (excluding the actor if they are an admin)
        $this->sendToRoles(
            $title,
            $staffMessage,
            'status_update',
            [User::ROLE_BFP_ADMIN],
            $actorId,
            $at,
            $reportId,
        );

        // 2. If action originated from Web Admin or System, fan out to Personnel and Resident
        $isPersonnelActor = $actor && $actor->role === User::ROLE_BFP_PERSONNEL;

        if (! $isPersonnelActor) {
            // Notify active BFP Personnel on mobile
            $this->sendToRoles(
                $title,
                $staffMessage,
                'status_update',
                [User::ROLE_BFP_PERSONNEL],
                $actorId,
                $at,
                $reportId,
            );

            // Notify Resident Submitter
            $this->notifyResidentStatus($report, $status, $at);
        }
    }

    /**
     * Active (pending/accepted/dispatched) reports submitted recently within
     * {@see self::DUPLICATE_RADIUS_METERS} of the given report.
     *
     * @return Collection<int, CommunityReport>
     */
    public function findNearbyActiveReports(CommunityReport $report): Collection
    {
        if ($report->latitude === null || $report->longitude === null) {
            return collect();
        }

        $latitude = (float) $report->latitude;
        $longitude = (float) $report->longitude;
        $latitudeDelta = self::DUPLICATE_RADIUS_METERS / 111320;
        $longitudeDelta = $latitudeDelta / max(cos(deg2rad($latitude)), 0.01);
        $reference = $report->created_at ?? now();

        return CommunityReport::query()
            ->whereKeyNot($report->getKey())
            ->whereIn('status', [
                CommunityReport::STATUS_PENDING,
                CommunityReport::STATUS_ACCEPTED,
                CommunityReport::STATUS_DISPATCHED,
            ])
            ->whereBetween('created_at', [$reference->copy()->subHours(self::DUPLICATE_WINDOW_HOURS), $reference])
            ->whereBetween('latitude', [$latitude - $latitudeDelta, $latitude + $latitudeDelta])
            ->whereBetween('longitude', [$longitude - $longitudeDelta, $longitude + $longitudeDelta])
            ->orderBy('report_id')
            ->get()
            ->filter(fn (CommunityReport $other) => Geo::distanceInMeters(
                $latitude,
                $longitude,
                (float) $other->latitude,
                (float) $other->longitude,
            ) <= self::DUPLICATE_RADIUS_METERS)
            ->values();
    }

    /**
     * Sends notification to the resident who submitted the report.
     */
    private function notifyResidentStatus(CommunityReport $report, string $status, ?CarbonInterface $at = null): void
    {
        if (! $report->user_id) {
            return;
        }

        $messages = [
            'accepted' => "Your fire report (#{$report->report_id}) has been accepted by BFP personnel.",
            'dispatched' => "BFP personnel have been dispatched for your fire report (#{$report->report_id}).",
            'resolved' => "Your fire report (#{$report->report_id}) has been marked as resolved by BFP personnel.",
            'invalid' => "Your fire report (#{$report->report_id}) has been marked as false or invalid by BFP personnel.",
        ];

        $message = $messages[$status] ?? "The status of your fire report (#{$report->report_id}) has been updated to: {$status}.";

        Notification::create([
            'user_id' => $report->user_id,
            'report_id' => (int) $report->report_id,
            'title' => 'Report Status Updated',
            'message' => $message,
            'notification_type' => 'status_update',
            'is_read' => false,
            'created_at' => $at ?? now(),
        ]);
    }

    /**
     * @param  list<string>  $roles
     */
    private function sendToRoles(
        string $title,
        string $message,
        string $type,
        array $roles,
        ?int $exceptUserId,
        ?CarbonInterface $at,
        ?int $reportId = null
    ): void {
        $recipientIds = User::query()
            ->whereIn('role', $roles)
            ->where('status', User::STATUS_ACTIVE)
            ->when($exceptUserId !== null, fn ($query) => $query->whereKeyNot($exceptUserId))
            ->pluck('user_id');

        if ($recipientIds->isEmpty()) {
            return;
        }

        $createdAt = $at ?? now();

        Notification::insert($recipientIds->map(fn (int $id) => [
            'user_id' => $id,
            'report_id' => $reportId,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'is_read' => false,
            'created_at' => $createdAt,
        ])->all());
    }

    private function reporterName(CommunityReport $report): string
    {
        return $report->reporter_name ?: ($report->reporter?->name ?: 'a resident');
    }

    private function actorName(?User $actor): string
    {
        if (! $actor) {
            return 'System';
        }

        $rank = $actor->personnelDetails?->rank;

        return trim(($rank ? "{$rank} " : '').$actor->name) ?: 'BFP personnel';
    }

    private function locationSuffix(CommunityReport $report): string
    {
        $barangayName = $report->barangay?->barangay_name;

        return $barangayName ? " in {$barangayName}" : '';
    }
}
