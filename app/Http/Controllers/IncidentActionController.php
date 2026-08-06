<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IncidentActionController extends Controller
{
    /**
     * Turns a pending report into an official incident: creates the
     * incident_record and flips the report to `verified`. bfp_admin only —
     * gated by the `bfp.admin` middleware on the route.
     */
    public function verify(Request $request, CommunityReport $report): RedirectResponse
    {
        $this->assertStatus($report, CommunityReport::STATUS_PENDING, 'verify');

        $data = $request->validate([
            'barangay_id' => ['required', 'integer', 'exists:barangay,barangay_id'],
            'incident_type' => ['required', Rule::in(['structural', 'grass', 'electrical', 'vehicular', 'other'])],
            'severity_level' => ['required', Rule::in(['low', 'moderate', 'high', 'critical'])],
            'cause_of_fire' => ['nullable', 'string', 'max:150'],
            'casualties' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($report, $data) {
            IncidentRecord::create([
                'report_id' => $report->report_id,
                'barangay_id' => $data['barangay_id'],
                'data_time' => now(),
                'incident_type' => $data['incident_type'],
                'severity_level' => $data['severity_level'],
                'cause_of_fire' => $data['cause_of_fire'] ?? null,
                'casualties' => $data['casualties'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $report->update(['status' => CommunityReport::STATUS_VERIFIED]);
        });

        $this->notifyReporter($report, 'Your fire incident report has been verified by BFP Lian personnel.');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Report verified — incident record created.',
        ]);
    }

    /**
     * bfp_admin only — gated by the `bfp.admin` middleware on the route.
     */
    public function reject(CommunityReport $report): RedirectResponse
    {
        $this->assertStatus($report, CommunityReport::STATUS_PENDING, 'reject');

        $report->update(['status' => CommunityReport::STATUS_REJECTED]);

        $this->notifyReporter($report, 'Your fire incident report could not be verified and has been closed.');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Report rejected.',
        ]);
    }

    /**
     * Advances an already-verified incident: verified -> dispatched ->
     * resolved. Open to any bfp staff (personnel are the ones actually en
     * route / on scene), unlike verify/reject which is an admin call.
     */
    public function updateStatus(Request $request, CommunityReport $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([CommunityReport::STATUS_DISPATCHED, CommunityReport::STATUS_RESOLVED])],
        ]);

        $allowedFrom = $data['status'] === CommunityReport::STATUS_DISPATCHED
            ? CommunityReport::STATUS_VERIFIED
            : CommunityReport::STATUS_DISPATCHED;

        $this->assertStatus($report, $allowedFrom, 'update the status of');

        $report->update(['status' => $data['status']]);

        $this->notifyReporter($report, $data['status'] === CommunityReport::STATUS_DISPATCHED
            ? 'Fire responders have been dispatched to the location you reported.'
            : 'The fire incident you reported has been marked as resolved.');

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Status updated to '.ucfirst($data['status']).'.',
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

    private function assertStatus(CommunityReport $report, string $expected, string $action): void
    {
        if ($report->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "Can't {$action} this report — its status has changed to \"{$report->status}\".",
            ]);
        }
    }
}
