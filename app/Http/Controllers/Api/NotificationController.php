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

        // Extract any linked report IDs (e.g. from "#123" in message)
        $reportIds = [];
        foreach ($notifications as $n) {
            if (preg_match('/#(\d+)/', (string) $n->message, $matches)) {
                $reportIds[] = (int) $matches[1];
            }
        }

        $reports = ! empty($reportIds)
            ? CommunityReport::whereIn('report_id', array_unique($reportIds))
                ->get(['report_id', 'latitude', 'longitude'])
                ->keyBy('report_id')
            : collect();

        $result = $notifications->map(function ($n) use ($reports) {
            $reportId = null;
            $lat = null;
            $lng = null;

            if (preg_match('/#(\d+)/', (string) $n->message, $matches)) {
                $matchedId = (int) $matches[1];
                if ($reports->has($matchedId)) {
                    $report = $reports->get($matchedId);
                    $reportId = (int) $report->report_id;
                    $lat = $report->latitude !== null ? (float) $report->latitude : null;
                    $lng = $report->longitude !== null ? (float) $report->longitude : null;
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
