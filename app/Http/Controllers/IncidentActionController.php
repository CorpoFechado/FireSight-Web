<?php

namespace App\Http\Controllers;

use App\Enums\AlarmLevel;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentDispatch;
use App\Models\IncidentDispatchPersonnel;
use App\Models\IncidentDispatchTruck;
use App\Models\IncidentRecord;
use App\Models\ReportEvidence;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Services\GeoBoundaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IncidentActionController extends Controller
{
    /**
     * Accepts a pending community report and assigns its barangay.
     * Does NOT create an incident_record — that is only created once the
     * incident is resolved with assessment details.
     * bfp_admin only — gated by the `bfp.admin` middleware on the route.
     */
    public function accept(Request $request, CommunityReport $report): RedirectResponse
    {
        $this->assertStatus($report, [CommunityReport::STATUS_PENDING], 'accept');

        $data = $request->validate([
            'barangay_id' => ['required', 'integer', 'exists:barangay,barangay_id'],
        ]);

        DB::transaction(function () use ($report, $data) {
            $report->update([
                'status' => CommunityReport::STATUS_ACCEPTED,
                'barangay_id' => $data['barangay_id'],
            ]);

            $barangay = Barangay::find($data['barangay_id']);
            $barangayName = $barangay ? $barangay->barangay_name : 'designated barangay';
            $this->logStatusHistory(
                $report,
                CommunityReport::STATUS_ACCEPTED,
                "Report verified and accepted by BFP dispatcher; assigned to {$barangayName}."
            );
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Report accepted.',
        ]);
    }

    /**
     * Marks a pending report as invalid.
     * bfp_admin only — gated by the `bfp.admin` middleware on the route.
     */
    public function invalidate(CommunityReport $report): RedirectResponse
    {
        $this->assertStatus($report, [CommunityReport::STATUS_PENDING], 'invalidate');

        DB::transaction(function () use ($report) {
            $report->update(['status' => CommunityReport::STATUS_INVALID]);
            $this->logStatusHistory(
                $report,
                CommunityReport::STATUS_INVALID,
                'Report investigated and marked as invalid / false alarm by BFP personnel.'
            );
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Report marked as invalid.',
        ]);
    }

    /**
     * Advances an accepted incident to dispatched.
     * Open to any bfp staff.
     */
    public function updateStatus(Request $request, CommunityReport $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([CommunityReport::STATUS_DISPATCHED])],
        ]);

        if ($report->status !== CommunityReport::STATUS_ACCEPTED) {
            throw ValidationException::withMessages([
                'status' => "Can't update the status of this report — its status has changed to \"{$report->status}\".",
            ]);
        }

        DB::transaction(function () use ($report, $data) {
            $report->update(['status' => $data['status']]);
            $this->logStatusHistory(
                $report,
                $data['status'],
                'Fire response units dispatched to the reported location.'
            );
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Status updated to Dispatched.',
        ]);
    }

    /**
     * Resolves the incident and creates a record ready for later assessment.
     * Open to any BFP staff.
     */
    public function resolve(CommunityReport $report): RedirectResponse
    {
        $this->assertStatus(
            $report,
            [CommunityReport::STATUS_DISPATCHED, CommunityReport::STATUS_ACCEPTED],
            'resolve'
        );

        DB::transaction(function () use ($report) {
            $now = now();

            $record = IncidentRecord::firstOrNew(['report_id' => $report->report_id]);
            $record->fill([
                'barangay_id' => $record->barangay_id ?? $report->barangay_id,
                'incident_datetime' => $record->incident_datetime ?? $now,
                'time_fire_out' => $record->time_fire_out ?? $now,
            ])->save();

            $report->update(['status' => CommunityReport::STATUS_RESOLVED]);

            $this->logStatusHistory(
                $report,
                CommunityReport::STATUS_RESOLVED,
                'Fire extinguished and incident marked as resolved.'
            );
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Report marked as resolved.',
        ]);
    }

    /**
     * Updates assessment details for a resolved incident record.
     * Open to any BFP staff / admin.
     */
    public function updateDetails(Request $request, CommunityReport $report): RedirectResponse
    {
        $this->assertStatus($report, [CommunityReport::STATUS_RESOLVED], 'update details for');

        $data = $request->validate($this->assessmentValidationRules());

        $record = IncidentRecord::firstOrNew(['report_id' => $report->report_id]);

        DB::transaction(function () use ($record, $report, $data) {
            if (! $record->exists) {
                $record->fill([
                    'barangay_id' => $report->barangay_id,
                    'incident_datetime' => $data['time_fire_started'] ?? now(),
                ]);
            }

            $this->fillAssessmentFields($record, $data)->save();

            $this->logStatusHistory(
                $report,
                $report->status,
                'Incident assessment details updated by BFP admin.'
            );
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Incident assessment details updated successfully.',
        ]);
    }

    /**
     * Manually logs an already-resolved incident report on behalf of a caller.
     * bfp_admin only — gated by the `bfp.admin` middleware on the route.
     */
    public function storeManual(Request $request, GeoBoundaryService $geoBoundaryService): RedirectResponse
    {
        $data = $request->validate([
            'reporter_name' => ['required', 'string', 'max:150'],
            'contact_number' => ['required', 'string', 'max:30'],
            'incident_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:2000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,heic,heif', 'max:10240'],
            'trucks' => ['required', 'array', 'min:1'],
            'trucks.*' => ['required', 'string', 'max:50'],
            'personnel_ids' => ['required', 'array', 'min:1'],
            'personnel_ids.*' => ['required', 'integer', 'exists:users,user_id'],
            ...$this->assessmentValidationRules(),
        ]);

        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];
        $barangayId = $geoBoundaryService->findBarangayByLocation($latitude, $longitude);

        $incidentDate = Carbon::parse($data['incident_date']);
        $createdAt = ! empty($data['time_fire_started'])
            ? Carbon::parse($data['time_fire_started'])
            : $incidentDate->copy()->setTimeFrom(now());
        $resolvedAt = ! empty($data['time_fire_out'])
            ? Carbon::parse($data['time_fire_out'])
            : now();

        $actor = auth()->user();
        $rank = $actor?->personnelDetails?->rank;
        $actorName = $actor
            ? trim(($rank ? "{$rank} " : '').$actor->name)
            : 'BFP admin';

        $report = DB::transaction(function () use ($data, $latitude, $longitude, $barangayId, $createdAt, $resolvedAt, $actor, $actorName, $request) {
            $report = new CommunityReport([
                'user_id' => $actor?->getKey(),
                'reporter_name' => trim($data['reporter_name']),
                'contact_number' => trim($data['contact_number']),
                'description' => trim($data['description']),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'device_latitude' => null,
                'device_longitude' => null,
                'barangay_id' => $barangayId,
                'status' => CommunityReport::STATUS_RESOLVED,
                'ai_fire_label' => null,
                'ai_fire_confidence' => null,
                'ai_verified_at' => null,
            ]);
            $report->created_at = $createdAt;
            $report->updated_at = $resolvedAt;
            $report->save();

            $record = new IncidentRecord([
                'report_id' => $report->report_id,
                'barangay_id' => $barangayId,
                'incident_datetime' => $createdAt,
            ]);
            $this->fillAssessmentFields($record, $data)->save();

            $dispatch = IncidentDispatch::create([
                'report_id' => $report->report_id,
                'dispatched_by' => $actor?->getKey(),
                'dispatched_at' => $createdAt,
            ]);

            foreach ($data['trucks'] as $truckName) {
                $cleaned = trim((string) $truckName);
                if ($cleaned !== '') {
                    IncidentDispatchTruck::create([
                        'dispatch_id' => $dispatch->dispatch_id,
                        'truck_name' => $cleaned,
                    ]);
                }
            }

            foreach (array_unique(array_map('intval', $data['personnel_ids'])) as $userId) {
                IncidentDispatchPersonnel::create([
                    'dispatch_id' => $dispatch->dispatch_id,
                    'user_id' => $userId,
                ]);
            }

            $photos = $request->file('photos', []);
            if (is_array($photos) && $photos !== []) {
                $this->storeEvidencePhotos($report, $photos, $actor?->getKey());
            }

            $this->logStatusHistory(
                $report,
                CommunityReport::STATUS_RESOLVED,
                "Incident report manually added as resolved by {$actorName}."
            );

            return $report;
        });

        return redirect()
            ->route('incidents.show', $report)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Manual incident report added as resolved.',
            ]);
    }

    /**
     * Uploads one or more fire scene evidence photos for an incident report.
     * Open to any BFP staff / admin.
     */
    public function storeEvidence(Request $request, CommunityReport $report): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'file', 'mimes:jpg,jpeg,png,heic,heif', 'max:10240'],
        ]);

        /** @var array<int, UploadedFile> $photos */
        $photos = $request->file('photos', []);
        $this->storeEvidencePhotos($report, $photos, auth()->user()?->getKey());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Scene photos uploaded successfully.',
        ]);
    }

    /**
     * Deletes a fire scene evidence photo from an incident report.
     * Open to any BFP staff / admin.
     */
    public function destroyEvidence(CommunityReport $report, ReportEvidence $evidence): RedirectResponse
    {
        if ((int) $evidence->report_id !== (int) $report->report_id) {
            abort(404);
        }

        if ($evidence->image_path) {
            $fullPath = storage_path('app/public/'.ltrim($evidence->image_path, '/'));
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        $evidence->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Scene photo deleted.',
        ]);
    }

    /**
     * Validation rules shared by manual report creation and assessment updates.
     *
     * @return array<string, mixed>
     */
    private function assessmentValidationRules(): array
    {
        return [
            'incident_type' => ['nullable', Rule::in([
                'residential_fire',
                'commercial_fire',
                'vehicular_fire',
                'storage_fire',
                'rubbish_fire',
                'others',
            ])],
            'alarm_level' => ['nullable', Rule::enum(AlarmLevel::class)],
            'cause_of_fire' => ['nullable', 'string', 'max:150'],
            'injuries' => ['nullable', 'integer', 'min:0'],
            'fatalities' => ['nullable', 'integer', 'min:0'],
            'casualties' => ['nullable', 'integer', 'min:0'],
            'estimated_damage' => ['nullable', 'numeric', 'min:0'],
            'establishment_name' => ['nullable', 'string', 'max:200'],
            'owner_name' => ['nullable', 'string', 'max:150'],
            'occupant_name' => ['nullable', 'string', 'max:150'],
            'time_fire_started' => ['nullable', 'date'],
            'time_fire_out' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Populates BFP assessment attributes on an IncidentRecord instance.
     *
     * @param  array<string, mixed>  $data
     */
    private function fillAssessmentFields(IncidentRecord $record, array $data): IncidentRecord
    {
        $injuries = isset($data['injuries']) ? (int) $data['injuries'] : 0;
        $fatalities = isset($data['fatalities']) ? (int) $data['fatalities'] : 0;
        $casualties = isset($data['casualties']) ? (int) $data['casualties'] : ($injuries + $fatalities);

        return $record->fill([
            'incident_type' => $data['incident_type'] ?? null,
            'alarm_level' => $data['alarm_level'] ?? null,
            'cause_of_fire' => $data['cause_of_fire'] ?? null,
            'injuries' => $injuries,
            'fatalities' => $fatalities,
            'casualties' => $casualties,
            'estimated_damage' => isset($data['estimated_damage']) && $data['estimated_damage'] !== '' ? (float) $data['estimated_damage'] : null,
            'establishment_name' => $data['establishment_name'] ?? null,
            'owner_name' => $data['owner_name'] ?? null,
            'occupant_name' => $data['occupant_name'] ?? null,
            'time_fire_started' => $data['time_fire_started'] ?? null,
            'time_fire_out' => $data['time_fire_out'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Stores uploaded scene photos in public storage and records them in `report_evidence`.
     *
     * @param  array<int, UploadedFile>  $photos
     */
    private function storeEvidencePhotos(CommunityReport $report, array $photos, ?int $uploadedBy): void
    {
        $destinationDir = storage_path('app/public/uploads/evidence');
        if (! File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        foreach ($photos as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $fileName = 'evidence_'.$report->report_id.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$extension;
            $file->move($destinationDir, $fileName);

            ReportEvidence::create([
                'report_id' => $report->report_id,
                'image_path' => 'uploads/evidence/'.$fileName,
                'uploaded_by' => $uploadedBy,
                'caption' => null,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Logs a status change entry to `report_status_history`.
     * `changed_by` is the currently authenticated user (NULL for system actions).
     */
    private function logStatusHistory(CommunityReport $report, string $status, ?string $notes = null): void
    {
        ReportStatusHistory::create([
            'report_id' => $report->report_id,
            'status' => $status,
            'notes' => $notes,
            'changed_by' => auth()->user()?->getKey(),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  list<string>  $expected
     */
    private function assertStatus(CommunityReport $report, array $expected, string $action): void
    {
        if (! in_array($report->status, $expected, true)) {
            throw ValidationException::withMessages([
                'status' => "Can't {$action} this report — its status has changed to \"{$report->status}\".",
            ]);
        }
    }
}
