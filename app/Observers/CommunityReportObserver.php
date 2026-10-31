<?php

namespace App\Observers;

use App\Models\CommunityReport;
use App\Models\User;
use App\Support\AdminNotifier;
use Illuminate\Support\Facades\Auth;

/**
 * Notifies staff about report activity. Hooked at the model level so
 * reports/status changes coming from any Eloquent entry point (web portal,
 * mobile API) are covered automatically.
 */
class CommunityReportObserver
{
    public function __construct(private AdminNotifier $adminNotifier) {}

    public function created(CommunityReport $report): void
    {
        $this->adminNotifier->reportSubmitted($report);
        $this->adminNotifier->possibleDuplicate($report, $this->adminNotifier->findNearbyActiveReports($report));
    }

    public function updated(CommunityReport $report): void
    {
        if ($report->wasChanged('status')) {
            $actor = Auth::user();

            $this->adminNotifier->statusChanged(
                $report,
                $report->status,
                $actor instanceof User ? $actor : null,
            );
        }
    }
}
