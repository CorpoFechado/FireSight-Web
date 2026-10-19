<?php

namespace App\Http\Controllers\Api;

use App\Models\CommunityReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    /**
     * BFP operational dashboard analytics.
     * Matches firesight-api/dashboard/analytics.php.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, [User::ROLE_BFP_PERSONNEL, User::ROLE_BFP_ADMIN], true)) {
            return $this->sendError('Unauthorized.', 403);
        }

        $activeIncidents = CommunityReport::whereIn('status', [
            CommunityReport::STATUS_PENDING,
            CommunityReport::STATUS_ACCEPTED,
            CommunityReport::STATUS_DISPATCHED,
        ])->count();

        $pendingVerification = CommunityReport::where('status', CommunityReport::STATUS_PENDING)->count();

        $resolvedToday = CommunityReport::where('status', CommunityReport::STATUS_RESOLVED)
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->count();

        $totalThisMonth = CommunityReport::where('status', CommunityReport::STATUS_RESOLVED)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return $this->sendSuccess([
            'active_incidents' => $activeIncidents,
            'pending_verification' => $pendingVerification,
            'resolved_today' => $resolvedToday,
            'total_this_month' => $totalThisMonth,
        ]);
    }
}
