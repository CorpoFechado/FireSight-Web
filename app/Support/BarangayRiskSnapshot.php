<?php

namespace App\Support;

use App\Enums\RiskLevel;
use App\Models\Barangay;

/**
 * Shared "latest risk assessment per barangay" query — used by both the
 * Dashboard mini-map and the full Fire Incidents Map so they never drift
 * out of sync with each other.
 */
class BarangayRiskSnapshot
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return Barangay::with(['riskAssessments' => fn ($q) => $q->orderByDesc('date')])
            ->get()
            ->map(function (Barangay $barangay) {
                $latest = $barangay->riskAssessments->first();

                $riskLevel = $latest?->risk_level;
                if ($riskLevel instanceof RiskLevel) {
                    $riskLevel = $riskLevel->value;
                }

                return [
                    'barangay_id' => $barangay->barangay_id,
                    'barangay_name' => $barangay->barangay_name,
                    'centroid_lat' => (float) $barangay->centroid_lat,
                    'centroid_lng' => (float) $barangay->centroid_lng,
                    'risk_level' => $riskLevel,
                    'prediction_score' => $latest ? (float) $latest->prediction_score : null,
                ];
            })
            ->all();
    }
}
