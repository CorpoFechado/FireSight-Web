<?php

namespace Database\Seeders;

use App\Models\CommunityReport;
use App\Models\Notification;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Support\AdminNotifier;
use Illuminate\Database\Seeder;

class AdminNotificationSeeder extends Seeder
{
    /**
     * Number of days (before the most recent report) to backfill.
     */
    private const BACKFILL_DAYS = 7;

    /**
     * Backfills admin notifications from the seeded report activity, since
     * model events (and therefore the CommunityReportObserver) are disabled
     * while seeding.
     */
    public function run(AdminNotifier $adminNotifier): void
    {
        $latestReport = CommunityReport::query()->latest('created_at')->first();

        if (! $latestReport) {
            return;
        }

        Notification::query()
            ->whereIn('user_id', User::query()->where('role', User::ROLE_BFP_ADMIN)->select('user_id'))
            ->delete();

        $since = $latestReport->created_at->copy()->subDays(self::BACKFILL_DAYS);

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
            ->each(function (CommunityReport $report) use ($adminNotifier) {
                $adminNotifier->reportSubmitted($report, $report->created_at);

                $adminNotifier->possibleDuplicate($report, $report->linkedFromReports, $report->created_at);

                $report->statusHistory
                    ->reject(fn (ReportStatusHistory $history) => $history->status === CommunityReport::STATUS_PENDING)
                    ->filter(fn (ReportStatusHistory $history) => $history->changedBy?->isBfpStaff() ?? false)
                    ->each(fn (ReportStatusHistory $history) => $adminNotifier->statusChanged(
                        $report,
                        $history->status,
                        $history->changedBy,
                        $history->created_at,
                    ));
            });
    }
}
