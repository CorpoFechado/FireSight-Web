<?php

namespace App\Http\Controllers\Api;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends ApiController
{
    /**
     * GET /api/v1/announcements (or /announcements/list.php)
     * Matches firesight-api/announcements/list.php.
     */
    public function index(): JsonResponse
    {
        $rows = Announcement::latest('created_at')
            ->limit(50)
            ->get();

        $result = $rows->map(function (Announcement $row) {
            return [
                'announcement_id' => (int) $row->announcement_id,
                'title' => $row->title,
                'content' => $row->content,
                'created_at' => $row->created_at?->format('Y-m-d H:i:s') ?? (string) $row->created_at,
            ];
        });

        return $this->sendSuccess($result);
    }
}
