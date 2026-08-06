<?php

namespace App\Support;

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

                return [
                    'barangay_id' => $barangay->barangay_id,
                    'barangay_name' => $barangay->barangay_name,
                    'latitude' => (float) $barangay->latitude,
                    'longitude' => (float) $barangay->longitude,
                    'risk_level' => $latest?->risk_level,
                    'prediction_score' => $latest ? (float) $latest->prediction_score : null,
                ];
            })
            ->all();
    }
}
