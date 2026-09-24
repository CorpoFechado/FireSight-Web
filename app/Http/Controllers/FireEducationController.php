<?php

namespace App\Http\Controllers;

use App\Models\FireEducationContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FireEducationController extends Controller
{
    /** @var array<int, string> */
    public const CATEGORIES = ['prevention', 'emergency_response', 'awareness'];

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $category = $request->string('category')->trim()->lower()->value();
        $featured = $request->string('featured')->trim()->lower()->value();

        $query = FireEducationContent::query()
            ->when($category !== '' && $category !== 'all' && in_array($category, self::CATEGORIES, true), function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->when($featured === 'yes', function ($q) {
                $q->where('is_featured', true);
            })
            ->when($featured === 'no', function ($q) {
                $q->where('is_featured', false);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            });

        $contents = $query
            ->orderByDesc('is_featured')
            ->latest('created_at')
            ->get()
            ->map(fn (FireEducationContent $content) => $this->toRow($content));

        return Inertia::render('fire-education/index', [
            'contents' => $contents,
            'filters' => [
                'search' => $search,
                'category' => $category ?: 'all',
                'featured' => $featured ?: 'all',
            ],
            'stats' => [
                'total' => FireEducationContent::count(),
                'prevention' => FireEducationContent::where('category', 'prevention')->count(),
                'emergency_response' => FireEducationContent::where('category', 'emergency_response')->count(),
                'awareness' => FireEducationContent::where('category', 'awareness')->count(),
                'featured' => FireEducationContent::where('is_featured', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'summary' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'read_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('fire_education', 'public');
        } elseif (! empty($data['image_url'])) {
            $imagePath = $data['image_url'];
        }

        $content = FireEducationContent::create([
            'title' => $data['title'],
            'category' => $data['category'],
            'summary' => $data['summary'],
            'body' => $data['body'],
            'read_minutes' => $data['read_minutes'],
            'is_featured' => $request->boolean('is_featured'),
            'image_path' => $imagePath,
            'created_at' => now(),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "\"{$content->title}\" was published.",
        ]);
    }

    public function update(Request $request, FireEducationContent $fireEducationContent): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'summary' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'read_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = $fireEducationContent->image_path;

        if ($request->boolean('remove_image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        } elseif ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('fire_education', 'public');
        } elseif ($request->has('image_url')) {
            $imagePath = $request->input('image_url') ?: null;
        }

        $fireEducationContent->update([
            'title' => $data['title'],
            'category' => $data['category'],
            'summary' => $data['summary'],
            'body' => $data['body'],
            'read_minutes' => $data['read_minutes'],
            'is_featured' => $request->boolean('is_featured'),
            'image_path' => $imagePath,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "\"{$fireEducationContent->title}\" was updated.",
        ]);
    }

    public function destroy(FireEducationContent $fireEducationContent): RedirectResponse
    {
        $title = $fireEducationContent->title;

        if ($fireEducationContent->image_path && Storage::disk('public')->exists($fireEducationContent->image_path)) {
            Storage::disk('public')->delete($fireEducationContent->image_path);
        }

        $fireEducationContent->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => "\"{$title}\" was deleted.",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(FireEducationContent $content): array
    {
        return [
            'id' => $content->content_id,
            'title' => $content->title,
            'category' => $content->category,
            'summary' => $content->summary,
            'body' => $content->body,
            'image_path' => $content->image_path,
            'read_minutes' => (int) $content->read_minutes,
            'is_featured' => (bool) $content->is_featured,
            'created_at' => $content->created_at?->format('Y-m-d H:i:s'),
            'created_date' => $content->created_at?->format('M d, Y') ?? '—',
        ];
    }
}
