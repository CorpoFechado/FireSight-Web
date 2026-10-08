<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);
        $isAdmin = $user->isBfpAdmin();

        $query = $isAdmin
            ? Notification::with('user')->latest('created_at')
            : $user->appNotifications()->latest('created_at');

        $notifications = $query->get();

        $todayGroup = $notifications->filter(fn (Notification $n) => $n->created_at->gte($today));
        $recent = $notifications->filter(fn (Notification $n) => $n->created_at->lt($today) && $n->created_at->gte($weekAgo));
        $earlier = $notifications->filter(fn (Notification $n) => $n->created_at->lt($weekAgo));

        return Inertia::render('notifications/index', [
            'isAdmin' => $isAdmin,
            'groups' => [
                'today' => $todayGroup->map(fn (Notification $n) => $this->toRow($n, $isAdmin))->values(),
                'recent' => $recent->map(fn (Notification $n) => $this->toRow($n, $isAdmin))->values(),
                'earlier' => $earlier->map(fn (Notification $n) => $this->toRow($n, $isAdmin))->values(),
            ],
            'unreadCount' => $notifications->where('is_read', false)->count(),
        ]);
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === request()->user()->id, 404);

        $notification->update(['is_read' => true]);

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        request()->user()->appNotifications()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(Notification $notification, bool $includeUser = false): array
    {
        $row = [
            'id' => $notification->notification_id,
            'type' => $notification->notification_type,
            'title' => $notification->title,
            'message' => $notification->message,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at->toIso8601String(),
        ];

        if ($includeUser && $notification->relationLoaded('user') && $notification->user instanceof User) {
            $row['user_name'] = $notification->user->name;
        }

        return $row;
    }
}
