<?php

namespace App\Http\Controllers\Api;

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends ApiController
{
    /**
     * GET /api/v1/map/incidents
     * Returns confirmed, mapped fire incidents for the resident-facing map.
     * Shows location and basic type/alarm level — no reporter PII.
     * Matches firesight-api/map/incidents.php.
     */
    public function incidents(): JsonResponse
    {
        $sql = "
            SELECT
                i.incident_id,
                COALESCE(i.barangay_id, r.barangay_id) AS barangay_id,
                b.barangay_name,
                r.latitude,
                r.longitude,
                i.incident_type,
                i.alarm_level,
                COALESCE(i.incident_datetime, r.created_at) AS data_time,
                i.cause_of_fire,
                i.casualties,
                i.notes,
                r.status
            FROM incident_record i
            JOIN community_report r ON r.report_id = i.report_id
            LEFT JOIN barangay b ON b.barangay_id = COALESCE(i.barangay_id, r.barangay_id)
            WHERE r.status IN ('accepted', 'dispatched', 'resolved')
              AND r.latitude IS NOT NULL
              AND r.longitude IS NOT NULL

            UNION ALL

            -- Fallback: verified/resolved reports without an incident_record yet
            SELECT
                NULL AS incident_id,
                r.barangay_id,
                b.barangay_name,
                r.latitude,
                r.longitude,
                NULL AS incident_type,
                NULL AS alarm_level,
                r.created_at AS data_time,
                NULL AS cause_of_fire,
                NULL AS casualties,
                NULL AS notes,
                r.status
            FROM community_report r
            LEFT JOIN barangay b ON b.barangay_id = r.barangay_id
            WHERE r.status IN ('accepted', 'dispatched', 'resolved')
              AND r.latitude IS NOT NULL
              AND r.longitude IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM incident_record i2 WHERE i2.report_id = r.report_id
              )

            ORDER BY data_time DESC
            LIMIT 200
        ";

        try {
            $rows = DB::select($sql);

            $result = array_map(function ($row) {
                return [
                    'incident_id' => $row->incident_id !== null ? (int) $row->incident_id : null,
                    'barangay_id' => (int) $row->barangay_id,
                    'barangay_name' => $row->barangay_name,
                    'latitude' => (float) $row->latitude,
                    'longitude' => (float) $row->longitude,
                    'incident_type' => $row->incident_type,
                    'alarm_level' => $row->alarm_level,
                    'data_time' => (string) $row->data_time,
                    'cause_of_fire' => $row->cause_of_fire,
                    'casualties' => $row->casualties !== null ? (int) $row->casualties : null,
                    'notes' => null, // Residents do not see internal BFP notes
                ];
            }, $rows);

            return $this->sendSuccess($result);
        } catch (\Throwable $e) {
            return $this->sendError('Database error: '.$e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/map/incidents-bfp
     * Richer incident data for BFP personnel with full reporter details.
     * Matches firesight-api/map/incidents_bfp.php.
     */
    public function incidentsBfp(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, [User::ROLE_BFP_PERSONNEL, User::ROLE_BFP_ADMIN], true)) {
            return $this->sendError('Unauthorized.', 403);
        }

        $year = $request->query('year');
        $status = $request->query('status');

        $query = DB::table('incident_record as i')
            ->join('community_report as r', 'r.report_id', '=', 'i.report_id')
            ->leftJoin('barangay as b', 'b.barangay_id', '=', DB::raw('COALESCE(i.barangay_id, r.barangay_id)'))
            ->whereIn('r.status', ['accepted', 'dispatched', 'resolved'])
            ->whereNotNull('r.latitude')
            ->whereNotNull('r.longitude')
            ->select([
                'i.incident_id',
                'r.report_id',
                DB::raw('COALESCE(i.barangay_id, r.barangay_id) AS barangay_id'),
                'b.barangay_name',
                'r.latitude',
                'r.longitude',
                'r.reporter_name',
                'r.contact_number',
                'r.description',
                'r.status',
                'r.created_at',
                'i.incident_type',
                'i.alarm_level',
                DB::raw('COALESCE(i.incident_datetime, r.created_at) AS data_time'),
                'i.cause_of_fire',
                'i.casualties',
                'i.notes',
            ]);

        if ($status && in_array($status, ['accepted', 'dispatched', 'resolved'], true)) {
            $query->where('r.status', $status);
        }

        if ($year && is_numeric($year)) {
            $query->whereRaw('YEAR(COALESCE(i.incident_datetime, r.created_at)) = ?', [(int) $year]);
        }

        $rows = $query->orderByDesc(DB::raw('COALESCE(i.incident_datetime, r.created_at)'))
            ->limit(500)
            ->get();

        $result = $rows->map(function ($row) {
            return [
                'incident_id' => $row->incident_id !== null ? (int) $row->incident_id : null,
                'report_id' => (int) $row->report_id,
                'barangay_id' => (int) $row->barangay_id,
                'barangay_name' => $row->barangay_name,
                'latitude' => (float) $row->latitude,
                'longitude' => (float) $row->longitude,
                'reporter_name' => $row->reporter_name,
                'contact_number' => $row->contact_number,
                'description' => $row->description,
                'status' => $row->status,
                'created_at' => (string) $row->created_at,
                'incident_type' => $row->incident_type,
                'alarm_level' => $row->alarm_level,
                'data_time' => (string) $row->data_time,
                'cause_of_fire' => $row->cause_of_fire,
                'casualties' => $row->casualties !== null ? (int) $row->casualties : null,
                'notes' => $row->notes,
            ];
        });

        return $this->sendSuccess($result);
    }

    /**
     * GET /api/v1/map/barangay-risk
     * Returns all 19 barangays with their latest risk assessment row.
     * Matches firesight-api/map/barangay_risk.php.
     */
    public function barangayRisk(): JsonResponse
    {
        $sql = "
            SELECT
                b.barangay_id,
                b.barangay_name,
                b.centroid_lat,
                b.centroid_lng,
                b.boundary_geojson,
                COALESCE(latest.risk_level, 'mild') AS risk_level,
                COALESCE(latest.prediction_score, 0) AS prediction_score
            FROM barangay b
            LEFT JOIN (
                SELECT ra1.barangay_id, ra1.risk_level, ra1.prediction_score
                FROM risk_assessment ra1
                INNER JOIN (
                    SELECT barangay_id, MAX(date) AS max_date
                    FROM risk_assessment
                    GROUP BY barangay_id
                ) ra2 ON ra1.barangay_id = ra2.barangay_id AND ra1.date = ra2.max_date
            ) latest ON latest.barangay_id = b.barangay_id
            ORDER BY b.barangay_name ASC
        ";

        $rows = DB::select($sql);

        $result = array_map(function ($row) {
            $rawRisk = strtolower((string) ($row->risk_level ?? 'mild'));
            if ($rawRisk === 'critical' || $rawRisk === 'high') {
                $normalizedRisk = 'high';
            } elseif ($rawRisk === 'moderate' || $rawRisk === 'medium') {
                $normalizedRisk = 'moderate';
            } else {
                $normalizedRisk = 'mild';
            }

            return [
                'barangay_id' => (int) $row->barangay_id,
                'barangay_name' => $row->barangay_name,
                'centroid_lat' => (float) $row->centroid_lat,
                'centroid_lng' => (float) $row->centroid_lng,
                'boundary_geojson' => is_array($row->boundary_geojson) ? json_encode($row->boundary_geojson) : $row->boundary_geojson,
                'risk_level' => $normalizedRisk,
                'prediction_score' => (float) $row->prediction_score,
            ];
        }, $rows);

        return $this->sendSuccess($result);
    }

    /**
     * GET /api/v1/reports/area-status
     * Powers the Home screen's "Area Status" card for the resident's barangay.
     * Matches firesight-api/reports/area_status.php.
     */
    public function areaStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->sendError('Authentication required. Please sign in again.', 401);
        }

        $address = ResidentAddress::where('user_id', $user->user_id)
            ->leftJoin('barangay', 'barangay.barangay_id', '=', 'resident_address.barangay_id')
            ->select([
                'resident_address.barangay_id',
                'barangay.barangay_name',
                'resident_address.municipality',
                'resident_address.province',
            ])
            ->first();

        $userMunicipality = trim((string) ($address->municipality ?? 'Lian'));
        $isLianResident = (strcasecmp($userMunicipality, 'Lian') === 0)
            && ! empty($address->barangay_id)
            && ! empty($address->barangay_name);

        if ($isLianResident) {
            $barangayId = (int) $address->barangay_id;
            $barangayName = $address->barangay_name;
            $isExternal = false;

            $riskRow = DB::table('risk_assessment')
                ->where('barangay_id', $barangayId)
                ->orderByDesc('date')
                ->first(['risk_level', 'prediction_score']);

            $incidentCount = DB::table('incident_record')
                ->where('barangay_id', $barangayId)
                ->whereBetween('incident_datetime', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            $rawRisk = strtolower((string) ($riskRow->risk_level ?? 'mild'));
        } else {
            $barangayId = null;
            $barangayName = 'All Barangays';
            $isExternal = true;

            $avgScoreRow = DB::selectOne('
                SELECT AVG(r.prediction_score) AS avg_score
                FROM risk_assessment r
                INNER JOIN (
                    SELECT barangay_id, MAX(date) AS max_date 
                    FROM risk_assessment 
                    GROUP BY barangay_id
                ) latest ON r.barangay_id = latest.barangay_id AND r.date = latest.max_date
            ');

            $avgScore = (float) ($avgScoreRow->avg_score ?? 0);

            $incidentCount = DB::table('incident_record')
                ->whereBetween('incident_datetime', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            if ($avgScore >= 0.66) {
                $rawRisk = 'high';
            } elseif ($avgScore >= 0.33) {
                $rawRisk = 'moderate';
            } else {
                $rawRisk = 'mild';
            }
        }

        $advisoryActive = Announcement::where('created_at', '>=', now()->subDays(7))->exists();

        if ($rawRisk === 'critical' || $rawRisk === 'high') {
            $normalizedRisk = 'high';
        } elseif ($rawRisk === 'moderate' || $rawRisk === 'medium') {
            $normalizedRisk = 'moderate';
        } else {
            $normalizedRisk = 'mild';
        }

        return $this->sendSuccess([
            'barangay_id' => $barangayId,
            'barangay_name' => $barangayName,
            'municipality' => $isExternal ? 'Lian' : ($address->municipality ?? 'Lian'),
            'province' => $isExternal ? 'Batangas' : ($address->province ?? 'Batangas'),
            'registered_municipality' => $address->municipality ?? null,
            'registered_province' => $address->province ?? null,
            'is_external' => $isExternal,
            'scope_label' => $isExternal ? 'PILOT COVERAGE · MUNICIPAL AVERAGE' : 'AREA STATUS',
            'pilot_station' => 'BFP Lian Fire Station',
            'risk_level' => $normalizedRisk,
            'incidents_this_month' => (int) $incidentCount,
            'advisory_active' => $advisoryActive,
        ]);
    }
}
