<?php

namespace App\Http\Controllers\Api;

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Services\FireVerificationService;
use App\Services\GeoBoundaryService;
use App\Services\MobileNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ReportController extends ApiController
{
    public function __construct(
        protected GeoBoundaryService $geoService,
        protected FireVerificationService $fireVerificationService,
        protected MobileNotificationService $notificationService,
    ) {}

    /**
     * Submit a fire report with photo as multipart form data.
     * Matches firesight-api/reports/create.php.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if ($latitude === null || $longitude === null) {
            return $this->sendError('Missing required field(s): latitude, longitude', 422);
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;
        $deviceLatitude = $request->filled('device_latitude') ? (float) $request->input('device_latitude') : null;
        $deviceLongitude = $request->filled('device_longitude') ? (float) $request->input('device_longitude') : null;
        $accuracy = $request->filled('location_accuracy_m') ? (float) $request->input('location_accuracy_m') : null;
        $locationName = trim((string) $request->input('location_name', ''));
        $description = trim((string) $request->input('description', ''));

        // Point-in-Polygon check: exact coordinates override user's registered barangay
        $pipBarangayId = $this->geoService->findBarangayByLocation($latitude, $longitude);
        $barangayId = $pipBarangayId;

        if ($barangayId === null && $request->filled('barangay_id')) {
            $checkId = (int) $request->input('barangay_id');
            if (Barangay::where('barangay_id', $checkId)->exists()) {
                $barangayId = $checkId;
            }
        }

        // Validate photo
        if (! $request->hasFile('report_image') || ! $request->file('report_image')->isValid()) {
            return $this->sendError('A photo is required to submit a fire report.', 422);
        }

        $file = $request->file('report_image');
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            return $this->sendError('Unsupported image type. Please use JPG, PNG, or HEIC.', 422);
        }

        $maxSizeBytes = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSizeBytes) {
            return $this->sendError('Image is too large. Maximum size is 10MB.', 422);
        }

        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/heic' => 'heic', 'image/heif' => 'heif'];
        $extension = $extMap[$mimeType] ?? ($file->getClientOriginalExtension() ?: 'jpg');
        $fileName = 'report_'.$user->user_id.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$extension;

        // Save into storage/app/public/uploads/reports
        $destinationDir = storage_path('app/public/uploads/reports');
        if (! File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        $file->move($destinationDir, $fileName);
        $relativePath = 'uploads/reports/'.$fileName;
        $absolutePath = $destinationDir.DIRECTORY_SEPARATOR.$fileName;

        // Run non-blocking AI verification
        $aiResult = $this->fireVerificationService->verifyFireImage($absolutePath);
        $aiLabel = $aiResult['label'];
        $aiConfidence = $aiResult['confidence'];
        $aiVerifiedAt = $aiLabel !== null ? now() : null;

        $reporterName = trim($user->first_name.' '.$user->last_name);

        DB::beginTransaction();
        try {
            $report = CommunityReport::create([
                'user_id' => $user->user_id,
                'reporter_name' => $reporterName,
                'contact_number' => $user->contact_number,
                'description' => $description,
                'report_image' => $relativePath,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'device_latitude' => $deviceLatitude,
                'device_longitude' => $deviceLongitude,
                'location_accuracy_m' => $accuracy,
                'barangay_id' => $barangayId,
                'status' => CommunityReport::STATUS_PENDING,
                'ai_fire_label' => $aiLabel,
                'ai_fire_confidence' => $aiConfidence,
                'ai_verified_at' => $aiVerifiedAt,
            ]);

            ReportStatusHistory::create([
                'report_id' => $report->report_id,
                'status' => CommunityReport::STATUS_PENDING,
                'notes' => 'Report submitted by resident.',
                'changed_by' => null,
                'created_at' => now(),
            ]);

            // Notify resident and active BFP personnel
            $this->notificationService->notifyResidentReportSubmitted($user->user_id, $report->report_id);
            $this->notificationService->notifyPersonnelOfNewReport($report->report_id, $locationName !== '' ? $locationName : null);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            @unlink($absolutePath);
            Log::error('[ReportController@store] Transaction failed: '.$e->getMessage());

            return $this->sendError('Unable to submit your report right now. Please try again.', 500);
        }

        $report->load('barangay');

        return $this->sendSuccess($this->formatReportRow($report), 201);
    }

    /**
     * List reports for resident (or all for staff).
     * Matches firesight-api/reports/list.php.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $mineOnly = $request->query('mine', '0') === '1';
        $limit = max(1, min(50, (int) $request->query('limit', 20)));

        $query = CommunityReport::with('barangay');

        if ($mineOnly || $user->role === User::ROLE_RESIDENT) {
            $query->where('user_id', $user->user_id);
        }

        $reports = $query->orderByDesc('created_at')->limit($limit)->get();

        return $this->sendSuccess($reports->map(fn ($r) => $this->formatReportRow($r))->all());
    }

    /**
     * Get report detail by ID.
     * Matches firesight-api/reports/detail.php.
     */
    public function show(Request $request, int|string|null $id = null): JsonResponse
    {
        $user = $request->user();
        $reportId = (int) ($id ?? $request->query('report_id', 0));

        if ($reportId <= 0) {
            return $this->sendError('Missing or invalid report_id.', 422);
        }

        $report = CommunityReport::with('barangay')->find($reportId);
        if (! $report) {
            return $this->sendError('Report not found.', 404);
        }

        if ($user->role === User::ROLE_RESIDENT && (int) $report->user_id !== (int) $user->user_id) {
            return $this->sendError('You do not have permission to view this report.', 403);
        }

        return $this->sendSuccess($this->formatReportRow($report));
    }

    /**
     * Get report status history (ordered oldest to newest).
     * Matches firesight-api/reports/status_history.php.
     */
    public function statusHistory(Request $request, int|string|null $id = null): JsonResponse
    {
        $user = $request->user();
        $reportId = (int) ($id ?? $request->query('report_id', 0));

        if ($reportId <= 0) {
            return $this->sendError('Missing or invalid report_id.', 422);
        }

        $report = CommunityReport::find($reportId);
        if (! $report) {
            return $this->sendError('Report not found.', 404);
        }

        if ($user->role === User::ROLE_RESIDENT && (int) $report->user_id !== (int) $user->user_id) {
            return $this->sendError('You do not have permission to view this report.', 403);
        }

        $history = ReportStatusHistory::where('report_id', $reportId)
            ->orderBy('created_at', 'asc')
            ->get();

        $result = $history->map(function ($row) {
            $createdAt = $row->created_at;
            if ($createdAt instanceof \DateTimeInterface) {
                $createdAt = $createdAt->format('Y-m-d H:i:s');
            }

            return [
                'history_id' => (int) $row->history_id,
                'status' => $row->status,
                'notes' => $row->notes,
                'created_at' => $createdAt,
            ];
        })->all();

        return $this->sendSuccess($result);
    }
}
