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

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $resolvedToday = CommunityReport::where('status', CommunityReport::STATUS_RESOLVED)
            ->where(function ($query) use ($todayStart, $todayEnd) {
                $query->whereHas('statusHistory', fn ($q) => $q->where('status', CommunityReport::STATUS_RESOLVED)->whereBetween('created_at', [$todayStart, $todayEnd]))
                    ->orWhere(function ($q) use ($todayStart, $todayEnd) {
                        $q->whereDoesntHave('statusHistory', fn ($sh) => $sh->where('status', CommunityReport::STATUS_RESOLVED))
                            ->where(fn ($sub) => $sub->whereBetween('updated_at', [$todayStart, $todayEnd])->orWhere(fn ($sq) => $sq->whereNull('updated_at')->whereBetween('created_at', [$todayStart, $todayEnd])));
                    });
            })
            ->count();

        $totalThisMonth = CommunityReport::where('status', CommunityReport::STATUS_RESOLVED)
            ->where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereHas('statusHistory', fn ($q) => $q->where('status', CommunityReport::STATUS_RESOLVED)->whereBetween('created_at', [$monthStart, $monthEnd]))
                    ->orWhere(function ($q) use ($monthStart, $monthEnd) {
                        $q->whereDoesntHave('statusHistory', fn ($sh) => $sh->where('status', CommunityReport::STATUS_RESOLVED))
                            ->where(fn ($sub) => $sub->whereBetween('updated_at', [$monthStart, $monthEnd])->orWhere(fn ($sq) => $sq->whereNull('updated_at')->whereBetween('created_at', [$monthStart, $monthEnd])));
                    });
            })
            ->count();

        return $this->sendSuccess([
            'active_incidents' => $activeIncidents,
            'pending_verification' => $pendingVerification,
            'resolved_today' => $resolvedToday,
            'total_this_month' => $totalThisMonth,
        ]);
    }
}
