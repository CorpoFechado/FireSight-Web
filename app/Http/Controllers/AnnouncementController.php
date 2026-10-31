<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $announcements = Announcement::query()
            ->with('creator:user_id,first_name,last_name')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhereHas('creator', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->get()
            ->map(fn (Announcement $announcement) => $this->toRow($announcement));

        return Inertia::render('announcements/index', [
            'announcements' => $announcements,
            'filters' => [
                'search' => $search,
            ],
            'totalCount' => Announcement::count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement = $request->user()->announcements()->create($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "\"{$announcement->title}\" was published.",
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement->update($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "\"{$announcement->title}\" was updated.",
        ]);
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "\"{$title}\" was deleted.",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(Announcement $announcement): array
    {
        return [
            'id' => $announcement->announcement_id,
            'title' => $announcement->title,
            'content' => $announcement->content,
            'date' => $announcement->created_at->format('Y-m-d'),
            'publishedBy' => $announcement->creator?->name ?? 'BFP Lian',
        ];
    }
}
