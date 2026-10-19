<?php

namespace App\Http\Controllers\Api;

use App\Models\FireEducationContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationController extends ApiController
{
    /**
     * GET /api/v1/education (or /education/list.php)
     * Matches firesight-api/education/list.php exactly.
     */
    public function index(): JsonResponse
    {
        $rows = FireEducationContent::orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->get();

        $result = $rows->map(function (FireEducationContent $row) {
            return $this->formatArticle($row);
        });

        return $this->sendSuccess($result);
    }

    /**
     * GET /api/v1/education/{id} (or /education/detail.php?content_id=X)
     * Matches firesight-api/education/detail.php exactly.
     */
    public function show(Request $request, ?int $id = null): JsonResponse
    {
        $contentId = $id ?? (int) ($request->query('content_id') ?? $request->query('id') ?? 0);

        if ($contentId <= 0) {
            return $this->sendError('Missing or invalid content_id.', 422);
        }

        $row = FireEducationContent::where('content_id', $contentId)->first();

        if (! $row) {
            return $this->sendError('Article not found.', 404);
        }

        return $this->sendSuccess($this->formatArticle($row));
    }

    /**
     * Formats an article row matching firesight-api output.
     *
     * @return array<string, mixed>
     */
    private function formatArticle(FireEducationContent $row): array
    {
        $imagePath = $row->image_path;

        if (! empty($imagePath) && ! str_starts_with($imagePath, 'http')) {
            $fullDiskPath = public_path('storage/'.ltrim((string) $imagePath, '/'));
            if (file_exists($fullDiskPath)) {
                $imagePath .= '?v='.filemtime($fullDiskPath);
            }
        }

        return [
            'content_id' => (int) $row->content_id,
            'title' => $row->title,
            'category' => $row->category,
            'summary' => $row->summary,
            'body' => $row->body,
            'image_path' => $imagePath,
            'is_featured' => (bool) $row->is_featured,
            'created_at' => $row->created_at?->format('Y-m-d H:i:s') ?? (string) $row->created_at,
        ];
    }
}
