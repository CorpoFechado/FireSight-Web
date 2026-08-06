<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\IncidentRecord;
use App\Support\BarangayRiskSnapshot;
use Inertia\Inertia;
use Inertia\Response;

class RiskAnalyticsController extends Controller
{
    public function index(): Response
    {
        $incidentCounts = IncidentRecord::query()
            ->selectRaw('barangay_id, count(*) as total')
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id');

        $ranking = collect(BarangayRiskSnapshot::all())
            ->map(fn (array $barangay) => [
                'barangay_id' => $barangay['barangay_id'],
                'barangay_name' => $barangay['barangay_name'],
                'risk_level' => $barangay['risk_level'],
                'score' => $barangay['prediction_score'] !== null
                    ? (int) round($barangay['prediction_score'] * 100)
                    : null,
                'incidentCount' => (int) ($incidentCounts[$barangay['barangay_id']] ?? 0),
            ])
            ->values();

        return Inertia::render('risk-analytics/index', [
            'barangayRisk' => BarangayRiskSnapshot::all(),
            'ranking' => $ranking,
            'incidentFrequency' => $ranking
                ->sortByDesc('incidentCount')
                ->take(10)
                ->map(fn (array $row) => [
                    'barangay_name' => $row['barangay_name'],
                    'incidentCount' => $row['incidentCount'],
                    'risk_level' => $row['risk_level'],
                ])
                ->values(),
            'totalBarangays' => Barangay::count(),
        ]);
    }
}
