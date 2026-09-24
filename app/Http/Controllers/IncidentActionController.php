<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\Notification;
use App\Models\ReportStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $this->notifyReporter($report, 'Your fire incident report has been accepted by BFP Lian personnel.');

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

        $this->notifyReporter($report, 'Your fire incident report could not be verified and has been marked as invalid.');

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

        $this->notifyReporter($report, 'Fire responders have been dispatched to the location you reported.');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Status updated to Dispatched.',
        ]);
    }

    /**
     * Resolves the incident: dispatched/accepted -> resolved.
     * This is where the assessment details are recorded, and ONLY now
     * is the community report inserted into the incident_record table.
     * Open to any bfp staff.
     */
    public function resolve(Request $request, CommunityReport $report): RedirectResponse
    {
        $this->assertStatus(
            $report,
            [CommunityReport::STATUS_DISPATCHED, CommunityReport::STATUS_ACCEPTED],
            'resolve'
        );

        if ($request->input('severity_level') === 'medium') {
            $request->merge(['severity_level' => 'moderate']);
        }

        $data = $request->validate([
            'incident_type' => ['required', Rule::in([
                'residential_fire',
                'commercial_fire',
                'vehicular_fire',
                'storage_fire',
                'rubbish_fire',
                'others',
            ])],
            'severity_level' => ['required', Rule::in(['low', 'moderate', 'high', 'critical'])],
            'cause_of_fire' => ['nullable', 'string', 'max:150'],
            'casualties' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($report, $data) {
            IncidentRecord::create([
                'report_id' => $report->report_id,
                'barangay_id' => $report->barangay_id,
                'incident_datetime' => now(),
                'incident_type' => $data['incident_type'],
                'severity_level' => $data['severity_level'],
                'cause_of_fire' => $data['cause_of_fire'] ?? null,
                'casualties' => $data['casualties'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $report->update(['status' => CommunityReport::STATUS_RESOLVED]);

            $historyNotes = ! empty($data['notes'])
                ? "Incident declared resolved: {$data['notes']}"
                : 'Fire declared extinguished; incident marked resolved by responding crew.';

            $this->logStatusHistory($report, CommunityReport::STATUS_RESOLVED, $historyNotes);
        });

        $this->notifyReporter($report, 'The fire incident you reported has been marked as resolved.');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Report resolved and incident record saved.',
        ]);
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
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);
    }

    private function notifyReporter(CommunityReport $report, string $message): void
    {
        Notification::create([
            'user_id' => $report->user_id,
            'title' => 'Report Status Update',
            'message' => $message,
            'notification_type' => 'status_update',
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
