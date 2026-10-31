<?php

namespace App\Http\Controllers\Api;

use App\Models\BarangayContact;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\ReportEvidence;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Services\MobileNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class IncidentController extends ApiController
{
    public function __construct(
        protected MobileNotificationService $notificationService,
    ) {}

    /**
     * Check that the authenticated user is BFP personnel or admin.
     */
    protected function authorizeBfp(Request $request): ?JsonResponse
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, [User::ROLE_BFP_PERSONNEL, User::ROLE_BFP_ADMIN], true)) {
            return $this->sendError('Unauthorized.', 403);
        }

        return null;
    }

    /**
     * Helper to fetch full incident data matching firesight-api's fetchIncident.
     *
     * @return array<string, mixed>|null
     */
    protected function fetchIncidentData(int $reportId): ?array
    {
        $row = CommunityReport::leftJoin('incident_record', 'community_report.report_id', '=', 'incident_record.report_id')
            ->leftJoin('barangay', function ($join) {
                $join->on('barangay.barangay_id', '=', DB::raw('COALESCE(incident_record.barangay_id, community_report.barangay_id)'));
            })
            ->where(function ($query) use ($reportId) {
                $query->where('community_report.report_id', $reportId)
                    ->orWhere('incident_record.incident_id', $reportId);
            })
            ->select([
                'community_report.*',
                'incident_record.incident_type',
                'incident_record.alarm_level',
                'incident_record.incident_datetime',
                'incident_record.cause_of_fire',
                'incident_record.casualties',
                'incident_record.notes',
                DB::raw('COALESCE(incident_record.barangay_id, community_report.barangay_id) as barangay_id'),
                'barangay.barangay_name',
            ])
            ->first();

        if (! $row) {
            return null;
        }

        return $this->formatIncidentRow($row->toArray());
    }

    /**
     * List incidents with filters for status, barangay, year, and search.
     * Matches firesight-api/incidents/list.php.
     */
    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $query = CommunityReport::leftJoin('incident_record', 'community_report.report_id', '=', 'incident_record.report_id')
            ->leftJoin('barangay', function ($join) {
                $join->on('barangay.barangay_id', '=', DB::raw('COALESCE(incident_record.barangay_id, community_report.barangay_id)'));
            })
            ->select([
                'community_report.report_id',
                'community_report.reporter_name',
                'community_report.contact_number',
                'community_report.description',
                'community_report.report_image',
                'community_report.latitude',
                'community_report.longitude',
                'community_report.location_accuracy_m',
                'community_report.status',
                'community_report.created_at',
                'community_report.ai_fire_label',
                'community_report.ai_fire_confidence',
                DB::raw('COALESCE(incident_record.barangay_id, community_report.barangay_id) as barangay_id'),
                'incident_record.incident_type',
                'incident_record.alarm_level',
                'incident_record.incident_datetime',
                'incident_record.cause_of_fire',
                'incident_record.casualties',
                'incident_record.notes',
                'barangay.barangay_name',
            ]);

        if ($request->filled('status')) {
            $query->where('community_report.status', $request->query('status'));
        }

        if ($request->filled('barangay_id')) {
            $query->where(DB::raw('COALESCE(incident_record.barangay_id, community_report.barangay_id)'), $request->query('barangay_id'));
        }

        if ($request->filled('year')) {
            $year = (int) $request->query('year');
            $startOfYear = Carbon::create($year, 1, 1, 0, 0, 0);
            $endOfYear = Carbon::create($year, 12, 31, 23, 59, 59);
            $query->whereBetween(
                DB::raw('COALESCE(incident_record.incident_datetime, community_report.created_at)'),
                [$startOfYear, $endOfYear]
            );
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('community_report.reporter_name', 'like', $search)
                    ->orWhere('community_report.description', 'like', $search)
                    ->orWhere('barangay.barangay_name', 'like', $search);
            });
        }

        $query->orderBy(DB::raw('COALESCE(incident_record.incident_datetime, community_report.created_at)'), 'desc')
            ->orderBy('community_report.created_at', 'desc')
            ->orderBy('community_report.report_id', 'desc');

        $rows = $query->get();

        return $this->sendSuccess($rows->map(fn ($r) => $this->formatIncidentRow($r->toArray()))->all());
    }

    /**
     * Get incident detail with evidence photos and barangay contacts.
     * Matches firesight-api/incidents/detail.php.
     */
    public function show(Request $request, int|string|null $id = null): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $reportId = (int) ($id ?? $request->query('report_id', 0));
        if ($reportId <= 0) {
            return $this->sendError('Missing report_id parameter.', 400);
        }

        $incident = $this->fetchIncidentData($reportId);
        if (! $incident) {
            return $this->sendError('Incident not found.', 404);
        }

        // Fetch evidence photos
        $evidence = ReportEvidence::where('report_id', $reportId)
            ->select(['evidence_id', 'image_path', 'caption'])
            ->get()
            ->toArray();
        $incident['evidence_photos'] = $evidence;

        // Fetch barangay contacts
        $contacts = [];
        if (! empty($incident['barangay_id'])) {
            $contacts = BarangayContact::where('barangay_id', $incident['barangay_id'])
                ->select(['name', 'role', 'phone_number'])
                ->get()
                ->toArray();
        }
        $incident['barangay_contacts'] = $contacts;

        return $this->sendSuccess($incident);
    }

    /**
     * Verify a pending incident report.
     * Matches firesight-api/incidents/verify.php.
     */
    public function verify(Request $request, int|string|null $id = null): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $user = $request->user();
        $reportId = (int) ($id ?? $request->input('report_id', 0));
        $notes = $request->input('notes');

        if ($reportId <= 0) {
            return $this->sendError('Missing report_id.', 400);
        }

        $report = CommunityReport::find($reportId);
        if (! $report) {
            return $this->sendError('Report not found.', 404);
        }

        if ($report->status !== CommunityReport::STATUS_PENDING) {
            return $this->sendError('Only pending reports can be verified.', 400);
        }

        DB::beginTransaction();
        try {
            $report->status = CommunityReport::STATUS_ACCEPTED;
            $report->save();

            ReportStatusHistory::create([
                'report_id' => $reportId,
                'status' => CommunityReport::STATUS_ACCEPTED,
                'notes' => $notes,
                'changed_by' => $user->user_id,
                'created_at' => now(),
            ]);

            $this->notificationService->notifyReportStatusChange($reportId, CommunityReport::STATUS_ACCEPTED, $user->user_id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[IncidentController@verify] Error: '.$e->getMessage());

            return $this->sendError('Database error: '.$e->getMessage(), 500);
        }

        return $this->sendSuccess($this->fetchIncidentData($reportId));
    }

    /**
     * Update incident status along accepted -> dispatched -> resolved.
     * Matches firesight-api/incidents/update_status.php.
     */
    public function updateStatus(Request $request, int|string|null $id = null): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $user = $request->user();
        $reportId = (int) ($id ?? $request->input('report_id', 0));
        $status = (string) $request->input('status', '');
        $notes = $request->input('notes');

        if ($reportId <= 0 || empty($status)) {
            return $this->sendError('Missing report_id or status.', 400);
        }

        $validStatuses = [CommunityReport::STATUS_ACCEPTED, CommunityReport::STATUS_DISPATCHED, CommunityReport::STATUS_RESOLVED];
        if (! in_array($status, $validStatuses, true)) {
            return $this->sendError('Invalid status value.', 400);
        }

        $report = CommunityReport::find($reportId);
        if (! $report) {
            return $this->sendError('Report not found.', 404);
        }

        DB::beginTransaction();
        try {
            $report->status = $status;
            $report->save();

            if ($status === CommunityReport::STATUS_RESOLVED) {
                $incident = IncidentRecord::where('report_id', $reportId)->first();
                if (! $incident) {
                    IncidentRecord::create([
                        'report_id' => $reportId,
                        'barangay_id' => $report->barangay_id,
                        'incident_datetime' => $report->created_at,
                    ]);
                } else {
                    $incident->barangay_id = $report->barangay_id;
                    $incident->incident_datetime = $report->created_at;
                    $incident->save();
                }
            }

            ReportStatusHistory::create([
                'report_id' => $reportId,
                'status' => $status,
                'notes' => $notes,
                'changed_by' => $user->user_id,
                'created_at' => now(),
            ]);

            $this->notificationService->notifyReportStatusChange($reportId, $status, $user->user_id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[IncidentController@updateStatus] Error: '.$e->getMessage());

            return $this->sendError('Database error: '.$e->getMessage(), 500);
        }

        return $this->sendSuccess($this->fetchIncidentData($reportId));
    }

    /**
     * Update incident record details (cause of fire, casualties, notes, alarm level, incident type).
     * Matches firesight-api/incidents/update_details.php.
     */
    public function updateDetails(Request $request, int|string|null $id = null): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $reportId = (int) ($id ?? $request->query('report_id') ?? $request->input('report_id', 0));
        if ($reportId <= 0) {
            return $this->sendError('Missing report_id.', 400);
        }

        $report = CommunityReport::find($reportId);
        if (! $report) {
            return $this->sendError('Report not found.', 404);
        }

        if ($report->status !== CommunityReport::STATUS_RESOLVED) {
            return $this->sendError('Incident details can only be saved after the report is resolved.', 409);
        }

        $incidentType = $request->input('incident_type');
        $validIncidentTypes = ['residential_fire', 'commercial_fire', 'vehicular_fire', 'storage_fire', 'rubbish_fire', 'others'];
        if ($incidentType && ! in_array($incidentType, $validIncidentTypes, true)) {
            $incidentType = null;
        }

        $alarmLevel = $request->input('alarm_level');
        $validAlarmLevels = [
            '1st_alarm', '2nd_alarm', '3rd_alarm', '4th_alarm', '5th_alarm',
            'task_force_alpha', 'task_force_bravo', 'task_force_charlie',
            'task_force_delta', 'task_force_echo', 'task_force_hotel',
            'task_force_india', 'general_alarm',
        ];
        if ($alarmLevel && ! in_array($alarmLevel, $validAlarmLevels, true)) {
            $alarmLevel = null;
        }

        $incident = IncidentRecord::where('report_id', $reportId)->first();

        if ($incident) {
            $incident->incident_type = $incidentType;
            $incident->alarm_level = $alarmLevel;
            $incident->cause_of_fire = $request->input('cause_of_fire');
            $incident->casualties = $request->filled('casualties') ? (int) $request->input('casualties') : 0;
            $incident->notes = $request->input('notes');
            $incident->save();
        } else {
            IncidentRecord::create([
                'report_id' => $reportId,
                'barangay_id' => $report->barangay_id ?? 1,
                'incident_datetime' => $report->created_at,
                'incident_type' => $incidentType,
                'alarm_level' => $alarmLevel,
                'cause_of_fire' => $request->input('cause_of_fire'),
                'casualties' => $request->filled('casualties') ? (int) $request->input('casualties') : 0,
                'notes' => $request->input('notes'),
            ]);
        }

        return $this->sendSuccess($this->fetchIncidentData($reportId));
    }

    /**
     * Get incident status history with changed_by_name.
     * Matches firesight-api/incidents/status_history.php.
     */
    public function statusHistory(Request $request, int|string|null $id = null): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $reportId = (int) ($id ?? $request->query('report_id') ?? $request->input('report_id', 0));
        if ($reportId <= 0) {
            return $this->sendError('Missing report_id parameter.', 400);
        }

        $history = ReportStatusHistory::leftJoin('users', 'report_status_history.changed_by', '=', 'users.user_id')
            ->where('report_status_history.report_id', $reportId)
            ->orderBy('report_status_history.created_at', 'asc')
            ->select([
                'report_status_history.history_id',
                'report_status_history.status',
                'report_status_history.notes',
                'report_status_history.created_at',
                DB::raw("CASE WHEN users.user_id IS NOT NULL THEN TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))) ELSE NULL END as changed_by_name"),
            ])
            ->get();

        $formatted = $history->map(function ($row) {
            $createdAt = $row->created_at;
            if ($createdAt instanceof \DateTimeInterface) {
                $createdAt = $createdAt->format('Y-m-d H:i:s');
            }

            return [
                'history_id' => (int) $row->history_id,
                'status' => $row->status,
                'notes' => $row->notes,
                'created_at' => $createdAt,
                'changed_by_name' => $row->changed_by_name,
            ];
        })->all();

        return $this->sendSuccess(['history' => $formatted]);
    }

    /**
     * Mark an incident report as invalid/false alarm.
     * Matches firesight-api/incidents/mark_invalid.php.
     */
    public function markInvalid(Request $request, int|string|null $id = null): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $user = $request->user();
        $reportId = (int) ($id ?? $request->input('report_id', 0));
        $reason = trim((string) $request->input('reason', ''));

        if ($reportId <= 0 || empty($reason)) {
            return $this->sendError('Missing report_id or reason.', 400);
        }

        $report = CommunityReport::find($reportId);
        if (! $report) {
            return $this->sendError('Report not found.', 404);
        }

        DB::beginTransaction();
        try {
            $report->status = CommunityReport::STATUS_INVALID;
            $report->save();

            ReportStatusHistory::create([
                'report_id' => $reportId,
                'status' => CommunityReport::STATUS_INVALID,
                'notes' => $reason,
                'changed_by' => $user->user_id,
                'created_at' => now(),
            ]);

            $this->notificationService->notifyReportStatusChange($reportId, CommunityReport::STATUS_INVALID, $user->user_id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[IncidentController@markInvalid] Error: '.$e->getMessage());

            return $this->sendError('Database error: '.$e->getMessage(), 500);
        }

        return $this->sendSuccess($this->fetchIncidentData($reportId));
    }

    /**
     * Upload additional evidence photo for an incident.
     * Matches firesight-api/incidents/add_evidence.php.
     */
    public function addEvidence(Request $request, int|string|null $id = null): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $user = $request->user();
        $reportId = (int) ($id ?? $request->input('report_id', 0));
        if ($reportId <= 0) {
            return $this->sendError('Missing report_id.', 400);
        }

        if (! $request->hasFile('photo') || ! $request->file('photo')->isValid()) {
            return $this->sendError('A photo is required.', 422);
        }

        $file = $request->file('photo');
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            return $this->sendError('Unsupported image type. Please use JPG, PNG, or HEIC.', 422);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->sendError('Image is too large. Maximum size is 10MB.', 422);
        }

        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/heic' => 'heic', 'image/heif' => 'heif'];
        $extension = $extMap[$mimeType] ?? ($file->getClientOriginalExtension() ?: 'jpg');
        $fileName = 'evidence_'.$reportId.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$extension;

        $destinationDir = storage_path('app/public/uploads/evidence');
        if (! File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        $file->move($destinationDir, $fileName);
        $relativePath = 'uploads/evidence/'.$fileName;

        ReportEvidence::create([
            'report_id' => $reportId,
            'image_path' => $relativePath,
            'uploaded_by' => $user->user_id,
            'caption' => $request->input('caption'),
            'created_at' => now(),
        ]);

        return $this->sendSuccess([
            'message' => 'Evidence added successfully.',
            'image_path' => $relativePath,
        ]);
    }

    /**
     * Manually enter an incident report by BFP personnel.
     * Matches firesight-api/incidents/create_manual.php.
     */
    public function createManual(Request $request): JsonResponse
    {
        if ($deny = $this->authorizeBfp($request)) {
            return $deny;
        }

        $user = $request->user();

        $barangayId = $request->input('barangay_id');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $description = trim((string) $request->input('description', ''));
        $incidentType = $request->input('incident_type');

        if (! $barangayId || ! $latitude || ! $longitude || ! $description || ! $incidentType) {
            return $this->sendError('barangay_id, latitude, longitude, description, and incident_type are required.', 422);
        }

        $validIncidentTypes = ['residential_fire', 'commercial_fire', 'vehicular_fire', 'storage_fire', 'rubbish_fire', 'others'];
        if (! in_array($incidentType, $validIncidentTypes, true)) {
            return $this->sendError('Invalid incident_type.', 422);
        }

        $alarmLevel = $request->input('alarm_level');
        $validAlarmLevels = [
            '1st_alarm', '2nd_alarm', '3rd_alarm', '4th_alarm', '5th_alarm',
            'task_force_alpha', 'task_force_bravo', 'task_force_charlie',
            'task_force_delta', 'task_force_echo', 'task_force_hotel',
            'task_force_india', 'general_alarm',
        ];
        if ($alarmLevel && ! in_array($alarmLevel, $validAlarmLevels, true)) {
            $alarmLevel = null;
        }

        $dataTimeRaw = $request->input('incident_datetime') ?? $request->input('data_time');
        $incidentDatetime = null;
        if ($dataTimeRaw) {
            try {
                $incidentDatetime = Carbon::parse($dataTimeRaw)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $incidentDatetime = now()->format('Y-m-d H:i:s');
            }
        }

        $causeOfFire = trim((string) $request->input('cause_of_fire', '')) ?: null;
        $casualties = $request->filled('casualties') ? (int) $request->input('casualties') : 0;
        $notes = trim((string) $request->input('notes', '')) ?: null;

        // Handle optional photo upload
        $reportImagePath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
            $mimeType = $file->getMimeType();

            if (! in_array($mimeType, $allowedMimeTypes, true)) {
                return $this->sendError('Unsupported image type. Use JPG, PNG, or HEIC.', 422);
            }
            if ($file->getSize() > 10 * 1024 * 1024) {
                return $this->sendError('Photo exceeds the 10 MB limit.', 422);
            }

            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/heic' => 'heic', 'image/heif' => 'heif'];
            $ext = $extMap[$mimeType] ?? ($file->getClientOriginalExtension() ?: 'jpg');
            $fileName = 'manual_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;

            $uploadDir = storage_path('app/public/uploads/reports');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $fileName);
            $reportImagePath = 'uploads/reports/'.$fileName;
        }

        DB::beginTransaction();
        try {
            $report = CommunityReport::create([
                'user_id' => $user->user_id,
                'reporter_name' => $user->first_name.' '.$user->last_name.' (BFP)',
                'contact_number' => $user->contact_number,
                'description' => $description,
                'report_image' => $reportImagePath,
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'barangay_id' => (int) $barangayId,
                'status' => CommunityReport::STATUS_ACCEPTED,
            ]);

            IncidentRecord::create([
                'report_id' => $report->report_id,
                'barangay_id' => (int) $barangayId,
                'incident_type' => $incidentType,
                'alarm_level' => $alarmLevel,
                'cause_of_fire' => $causeOfFire,
                'casualties' => $casualties,
                'notes' => $notes,
                'incident_datetime' => $incidentDatetime,
            ]);

            ReportStatusHistory::create([
                'report_id' => $report->report_id,
                'status' => CommunityReport::STATUS_ACCEPTED,
                'notes' => 'Manually entered by BFP personnel.',
                'changed_by' => $user->user_id,
                'created_at' => now(),
            ]);

            DB::commit();

            return $this->sendSuccess($this->fetchIncidentData($report->report_id), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($reportImagePath) {
                @unlink(storage_path('app/public/'.$reportImagePath));
            }
            Log::error('[IncidentController@createManual] Error: '.$e->getMessage());

            return $this->sendError('Database error: '.$e->getMessage(), 500);
        }
    }
}
