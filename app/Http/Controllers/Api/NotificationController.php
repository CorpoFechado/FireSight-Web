<?php

namespace App\Http\Controllers\Api;

use App\Models\CommunityReport;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    /**
     * List current user's notifications (up to 50, latest first) with linked report details.
     * Matches firesight-api/notifications/list.php.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->user_id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Collect report IDs directly from the report_id column or fallback to regex
        $reportIds = [];
        foreach ($notifications as $n) {
            if ($n->report_id) {
                $reportIds[] = (int) $n->report_id;
            } elseif (preg_match('/#(\d+)/', (string) $n->message.' '.(string) $n->title, $matches)) {
                $reportIds[] = (int) $matches[1];
            }
        }

        $reports = ! empty($reportIds)
            ? CommunityReport::whereIn('report_id', array_unique($reportIds))
                ->get(['report_id', 'latitude', 'longitude'])
                ->keyBy('report_id')
            : collect();

        // For resident notifications that don't have report_id or "#123" (legacy), map to user's reports
        $residentReports = $user->role === 'resident'
            ? CommunityReport::where('user_id', $user->user_id)->orderByDesc('created_at')->get(['report_id', 'created_at'])
            : collect();

        $result = $notifications->map(function ($n) use ($reports, $residentReports) {
            $reportId = $n->report_id ? (int) $n->report_id : null;
            $lat = null;
            $lng = null;

            if ($reportId && $reports->has($reportId)) {
                $report = $reports->get($reportId);
                $lat = $report->latitude !== null ? (float) $report->latitude : null;
                $lng = $report->longitude !== null ? (float) $report->longitude : null;
            } elseif (preg_match('/#(\d+)/', (string) $n->message.' '.(string) $n->title, $matches)) {
                $matchedId = (int) $matches[1];
                $reportId = $matchedId;
                if ($reports->has($matchedId)) {
                    $report = $reports->get($matchedId);
                    $lat = $report->latitude !== null ? (float) $report->latitude : null;
                    $lng = $report->longitude !== null ? (float) $report->longitude : null;
                }
            } elseif ($residentReports->isNotEmpty() && in_array($n->notification_type, ['incident_alert', 'status_update'])) {
                // Find closest report created on or before this notification
                $closestReport = $residentReports->first(function ($r) use ($n) {
                    return $r->created_at <= $n->created_at;
                }) ?? $residentReports->first();

                if ($closestReport) {
                    $reportId = (int) $closestReport->report_id;
                }
            }

            $createdAt = $n->created_at;
            if ($createdAt instanceof \DateTimeInterface) {
                $createdAt = $createdAt->format('Y-m-d H:i:s');
            }

            return [
                'notification_id' => (int) $n->notification_id,
                'title' => $n->title,
                'message' => $n->message,
                'notification_type' => $n->notification_type,
                'is_read' => (bool) $n->is_read,
                'created_at' => $createdAt,
                'report_id' => $reportId,
                'latitude' => $lat,
                'longitude' => $lng,
            ];
        })->all();

        return $this->sendSuccess($result);
    }

    /**
     * Mark a single notification or all notifications as read.
     * Matches firesight-api/notifications/mark_read.php.
     */
    public function markRead(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->boolean('all')) {
            Notification::where('user_id', $user->user_id)->update(['is_read' => true]);

            return $this->sendSuccess(['message' => 'All notifications marked as read.']);
        }

        if ($request->filled('notification_id')) {
            $notificationId = (int) $request->input('notification_id');
            Notification::where('notification_id', $notificationId)
                ->where('user_id', $user->user_id)
                ->update(['is_read' => true]);

            return $this->sendSuccess(['message' => 'Notification marked as read.']);
        }

        return $this->sendError('Missing required field(s): notification_id', 422);
    }
}
