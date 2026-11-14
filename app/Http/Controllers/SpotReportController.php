<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;
use App\Services\SpotReport\SpotReportGenerator;
use App\Support\SpotReport\SpotReportData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SpotReportController extends Controller
{
    /**
     * Download the Spot Investigation Report (SIR) of a resolved incident
     * with a complete BFP assessment as an editable Word document.
     */
    public function __invoke(Request $request, CommunityReport $report, SpotReportGenerator $generator): BinaryFileResponse|RedirectResponse
    {
        $report->load(['incidentRecord.barangay', 'barangay']);

        $blockingReasons = SpotReportData::blockingReasons($report);

        if ($blockingReasons !== []) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Cannot export Spot Report yet. Missing: '.implode(', ', $blockingReasons).'.',
            ]);
        }

        $data = SpotReportData::fromReport($report);
        $path = $generator->generate($data);

        // Logged to the application log rather than report_status_history so that
        // exports do not count as new "resolved" events in dashboard metrics.
        Log::info('Spot Investigation Report exported.', [
            'report_id' => $report->report_id,
            'reference' => $data->reference,
            'user_id' => $request->user()?->getKey(),
        ]);

        return response()
            ->download($path, $data->filename(), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->deleteFileAfterSend();
    }
}
