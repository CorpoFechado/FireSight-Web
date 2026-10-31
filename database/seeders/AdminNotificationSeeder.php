<?php

namespace Database\Seeders;

use App\Models\CommunityReport;
use App\Models\Notification;
use App\Models\User;
use App\Support\AdminNotifier;
use Illuminate\Database\Seeder;

class AdminNotificationSeeder extends Seeder
{
    /**
     * Number of days (before the most recent report) to backfill.
     */
    private const BACKFILL_DAYS = 14;

    /**
     * Backfills notifications across all three user roles (Resident, BFP Personnel, BFP Admin)
     * matching the exact production notification architecture.
     */
    public function run(AdminNotifier $adminNotifier): void
    {
        $latestReport = CommunityReport::query()->latest('created_at')->first();

        if (! $latestReport) {
            return;
        }

        // Wipe all existing notifications so every notification in the system has a valid report_id
        Notification::query()->delete();

        $adminIds = User::query()
            ->where('role', User::ROLE_BFP_ADMIN)
            ->where('status', User::STATUS_ACTIVE)
            ->pluck('user_id');

        $personnelIds = User::query()
            ->where('role', User::ROLE_BFP_PERSONNEL)
            ->where('status', User::STATUS_ACTIVE)
            ->pluck('user_id');

        $since = $latestReport->created_at->copy()->subDays(self::BACKFILL_DAYS);

        $statusLabels = [
            CommunityReport::STATUS_PENDING => 'Pending',
            CommunityReport::STATUS_ACCEPTED => 'Accepted',
            CommunityReport::STATUS_DISPATCHED => 'Dispatched',
            CommunityReport::STATUS_RESOLVED => 'Resolved',
            CommunityReport::STATUS_INVALID => 'Invalid',
        ];

        CommunityReport::query()
            ->with([
                'barangay',
                'reporter',
                'linkedFromReports',
                'statusHistory' => fn ($query) => $query->with('changedBy.personnelDetails')->orderBy('created_at'),
            ])
            ->where('created_at', '>=', $since)
            ->orderBy('created_at')
            ->get()
            ->each(function (CommunityReport $report) use ($adminIds, $personnelIds, $statusLabels, $latestReport) {
                $reportId = (int) $report->report_id;
                $barangayName = $report->barangay?->barangay_name;
                $locationSuffix = $barangayName ? " in {$barangayName}" : '';
                $locationName = $barangayName ?: 'submitted location';
                $reporterName = $report->reporter_name ?: ($report->reporter?->name ?: 'a resident');

                $confidence = $report->ai_fire_confidence !== null ? (float) $report->ai_fire_confidence : 0.945;
                $aiSummary = sprintf('The image assessment indicates that fire was detected with %.1f%% confidence.', $confidence * 100);
                $isRecentReport = $report->created_at >= $latestReport->created_at->copy()->subDays(2);

                $notificationsToInsert = [];

                // ── 1. Resident: Confirmation Notification ───────────────────────
                if ($report->user_id) {
                    $notificationsToInsert[] = [
                        'user_id' => $report->user_id,
                        'report_id' => $reportId,
                        'title' => 'Report Submitted',
                        'message' => "Your fire report (#{$reportId}) has been received and is under review by BFP Lian.",
                        'notification_type' => 'incident_alert',
                        'is_read' => true,
                        'created_at' => $report->created_at,
                    ];
                }

                // ── 2. BFP Personnel: New Incident Alert (Mobile) ───────────────
                foreach ($personnelIds as $personnelId) {
                    $notificationsToInsert[] = [
                        'user_id' => $personnelId,
                        'report_id' => $reportId,
                        'title' => "Fire Report near {$locationName}",
                        'message' => "A new fire report (#{$reportId}) was submitted near {$locationName}. {$aiSummary}",
                        'notification_type' => 'incident_alert',
                        'is_read' => ! $isRecentReport,
                        'created_at' => $report->created_at,
                    ];
                }

                // ── 3. BFP Admin: New Community Report (Web) ────────────────────
                foreach ($adminIds as $adminId) {
                    $notificationsToInsert[] = [
                        'user_id' => $adminId,
                        'report_id' => $reportId,
                        'title' => 'New Community Report',
                        'message' => "Report #{$reportId} from {$reporterName}{$locationSuffix} is awaiting verification.",
                        'notification_type' => 'incident_alert',
                        'is_read' => ! $isRecentReport,
                        'created_at' => $report->created_at,
                    ];
                }

                // ── 4. BFP Admin: Duplicate Report Detection ────────────────────
                if ($report->linkedFromReports->isNotEmpty()) {
                    $reportList = $report->linkedFromReports
                        ->map(fn (CommunityReport $nearby) => "#{$nearby->report_id}")
                        ->implode(', ');
                    $noun = $report->linkedFromReports->count() === 1 ? 'Report' : 'Reports';

                    foreach ($adminIds as $adminId) {
                        $notificationsToInsert[] = [
                            'user_id' => $adminId,
                            'report_id' => $reportId,
                            'title' => 'Possible Duplicate Report',
                            'message' => "Report #{$reportId} was submitted near active {$noun} {$reportList}. Review and link them if they describe the same incident.",
                            'notification_type' => 'incident_alert',
                            'is_read' => false,
                            'created_at' => $report->created_at,
                        ];
                    }
                }

                // ── 5. Status History Transitions ───────────────────────────────
                foreach ($report->statusHistory as $history) {
                    if ($history->status === CommunityReport::STATUS_PENDING) {
                        continue;
                    }

                    $actor = $history->changedBy;
                    $actorId = $actor?->getKey();
                    $rank = $actor?->personnelDetails?->rank;
                    $actorName = $actor ? trim(($rank ? "{$rank} " : '').$actor->name) : 'BFP personnel';
                    if (empty($actorName)) {
                        $actorName = 'BFP personnel';
                    }

                    $label = $statusLabels[$history->status] ?? ucfirst($history->status);
                    $staffTitle = $history->status === CommunityReport::STATUS_INVALID ? 'Report Marked Invalid' : "Report {$label}";
                    $staffMessage = "{$actorName} marked Report #{$reportId}{$locationSuffix} as {$label}.";

                    // Notify BFP Admins (excluding the actor if actor is admin)
                    foreach ($adminIds as $adminId) {
                        if ($actorId !== null && (int) $adminId === (int) $actorId) {
                            continue;
                        }

                        $notificationsToInsert[] = [
                            'user_id' => $adminId,
                            'report_id' => $reportId,
                            'title' => $staffTitle,
                            'message' => $staffMessage,
                            'notification_type' => 'status_update',
                            'is_read' => ! $isRecentReport,
                            'created_at' => $history->created_at,
                        ];
                    }

                    // Notify BFP Personnel (excluding the actor if actor is personnel)
                    foreach ($personnelIds as $personnelId) {
                        if ($actorId !== null && (int) $personnelId === (int) $actorId) {
                            continue;
                        }

                        $notificationsToInsert[] = [
                            'user_id' => $personnelId,
                            'report_id' => $reportId,
                            'title' => $staffTitle,
                            'message' => $staffMessage,
                            'notification_type' => 'status_update',
                            'is_read' => ! $isRecentReport,
                            'created_at' => $history->created_at,
                        ];
                    }

                    // Notify Resident Reporter
                    if ($report->user_id) {
                        $residentMessages = [
                            'accepted' => "Your fire report (#{$reportId}) has been accepted by BFP personnel.",
                            'dispatched' => "BFP personnel have been dispatched for your fire report (#{$reportId}).",
                            'resolved' => "Your fire report (#{$reportId}) has been marked as resolved by BFP personnel.",
                            'invalid' => "Your fire report (#{$reportId}) has been marked as false or invalid by BFP personnel.",
                        ];

                        $residentMessage = $residentMessages[$history->status] ?? "The status of your fire report (#{$reportId}) has been updated to: {$history->status}.";

                        $notificationsToInsert[] = [
                            'user_id' => $report->user_id,
                            'report_id' => $reportId,
                            'title' => 'Report Status Updated',
                            'message' => $residentMessage,
                            'notification_type' => 'status_update',
                            'is_read' => ! $isRecentReport,
                            'created_at' => $history->created_at,
                        ];
                    }
                }

                if (! empty($notificationsToInsert)) {
                    Notification::insert($notificationsToInsert);
                }
            });
    }
}
