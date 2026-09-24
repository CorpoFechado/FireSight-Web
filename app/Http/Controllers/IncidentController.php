<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\ReportStatusHistory;
use App\Support\Geo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    private const STATUSES = [
        CommunityReport::STATUS_PENDING,
        CommunityReport::STATUS_ACCEPTED,
        CommunityReport::STATUS_DISPATCHED,
        CommunityReport::STATUS_RESOLVED,
        CommunityReport::STATUS_INVALID,
    ];

    public function index(Request $request): Response
    {
        $status = $request->string('status')->lower()->value() ?: CommunityReport::STATUS_PENDING;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->string('date_from')->trim()->value();
        $dateTo = $request->string('date_to')->trim()->value();
        $barangayId = $request->integer('barangay_id') ?: null;
        $incidentType = $request->string('incident_type')->trim()->value();
        $severityLevel = $request->string('severity_level')->trim()->value();

        $reports = CommunityReport::query()
            ->with(['incidentRecord.barangay', 'barangay'])
            ->when(in_array($status, self::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('reporter_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhereHas('incidentRecord.barangay', fn ($q) => $q->where('barangay_name', 'like', "%{$search}%"))
                        ->orWhereHas('barangay', fn ($q) => $q->where('barangay_name', 'like', "%{$search}%"));
                });
            })
            ->when($dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($barangayId !== null, fn ($q) => $q->where(function ($q) use ($barangayId) {
                $q->where('barangay_id', $barangayId)
                    ->orWhereHas('incidentRecord', fn ($q) => $q->where('barangay_id', $barangayId));
            }))
            ->when($incidentType !== '', fn ($q) => $q->whereHas('incidentRecord', fn ($q) => $q->where('incident_type', $incidentType)))
            ->when($severityLevel !== '', fn ($q) => $q->whereHas('incidentRecord', fn ($q) => $q->where('severity_level', $severityLevel)))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (CommunityReport $report) => $this->toRow($report));

        return Inertia::render('incidents/index', [
            'reports' => $reports,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'barangay_id' => $barangayId ? (string) $barangayId : '',
                'incident_type' => $incidentType,
                'severity_level' => $severityLevel,
            ],
            'barangays' => Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name']),
            'totalCount' => CommunityReport::count(),
        ]);
    }

    public function show(CommunityReport $report): Response
    {
        $report->load([
            'barangay',
            'incidentRecord.barangay',
            'linkedReports.incidentRecord.barangay',
            'linkedReports.barangay',
            'linkedFromReports.incidentRecord.barangay',
            'linkedFromReports.barangay',
            'statusHistory.changedBy.personnelDetails',
        ]);

        // report_link is directional (main/related); merge both sides so
        // either report in a linked pair shows the other.
        $linked = $report->linkedReports
            ->merge($report->linkedFromReports)
            ->unique('report_id')
            ->map(fn (CommunityReport $r) => [
                'report_id' => $r->report_id,
                'reference' => $this->reference($r),
                'status' => $r->status,
            ])
            ->values();

        $statusHistory = $report->statusHistory
            ->sortBy('created_at')
            ->values()
            ->map(fn (ReportStatusHistory $history) => $this->toStatusHistoryRow($history, $report))
            ->values();

        if ($statusHistory->isEmpty()) {
            $statusHistory = collect([
                [
                    'id' => 'initial-'.$report->report_id,
                    'status' => $report->status,
                    'notes' => 'Incident report logged in FireSight.',
                    'changed_at' => $report->created_at->format('Y-m-d H:i'),
                    'changed_by' => $report->reporter_name ? "{$report->reporter_name} (Reporter)" : 'Citizen Reporter',
                    'personnel' => null,
                ],
            ]);
        }

        return Inertia::render('incidents/show', [
            'report' => [
                ...$this->toRow($report),
                'description' => $report->description,
                'report_image' => $report->report_image,
                'latitude' => (float) $report->latitude,
                'longitude' => (float) $report->longitude,
                'cause_of_fire' => $report->incidentRecord?->cause_of_fire,
                'casualties' => $report->incidentRecord?->casualties,
                'notes' => $report->incidentRecord?->notes,
            ],
            'statusHistory' => $statusHistory,
            'linkedReports' => $linked,
            'barangays' => Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name']),
            'suggestedBarangayId' => $report->status === CommunityReport::STATUS_PENDING
                ? $this->suggestBarangayId($report)
                : null,
        ]);
    }

    /**
     * Pre-fill guess for the accept form — not authoritative, the admin can
     * always pick a different barangay. Tries an exact polygon match first
     * (point-in-polygon against `barangay.boundary`), and only falls back
     * to nearest-centroid distance if the report's coordinates don't land
     * inside any known boundary.
     */
    private function suggestBarangayId(CommunityReport $report): ?int
    {
        return $this->barangayContainingPoint($report)?->barangay_id
            ?? $this->nearestBarangayId($report);
    }

    private function barangayContainingPoint(CommunityReport $report): ?Barangay
    {
        $lat = (float) $report->latitude;
        $lng = (float) $report->longitude;

        return Barangay::query()
            ->whereNotNull('boundary')
            ->get()
            ->first(fn (Barangay $b) => Geo::pointInPolygon($lat, $lng, $b->boundary));
    }

    private function nearestBarangayId(CommunityReport $report): ?int
    {
        return Barangay::query()
            ->whereNotNull('latitude')
            ->get()
            ->sortBy(fn (Barangay $b) => (($b->latitude - $report->latitude) ** 2) + (($b->longitude - $report->longitude) ** 2))
            ->first()
            ?->barangay_id;
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(CommunityReport $report): array
    {
        return [
            'report_id' => $report->report_id,
            'reference' => $this->reference($report),
            'reporter_name' => $report->reporter_name,
            'contact_number' => $report->contact_number,
            'barangay' => $report->barangay?->barangay_name ?? $report->incidentRecord?->barangay?->barangay_name,
            'type' => $report->incidentRecord?->incident_type
                ? AnalyticsController::TYPE_LABELS[$report->incidentRecord->incident_type] ?? ucfirst(str_replace('_', ' ', $report->incidentRecord->incident_type))
                : null,
            'severity' => $report->incidentRecord?->severity_level,
            'status' => $report->status,
            'dateTime' => $report->created_at->format('Y-m-d H:i'),
        ];
    }

    private function reference(CommunityReport $report): string
    {
        return sprintf('INC-%s-%04d', $report->created_at->format('Y'), $report->report_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function toStatusHistoryRow(ReportStatusHistory $history, CommunityReport $report): array
    {
        $user = $history->changedBy;
        $rank = $user?->personnelDetails?->rank;

        $changedByName = match (true) {
            $user && $user->isBfpStaff() => $rank ? "{$rank} {$user->name}" : $user->name,
            $user && $user->isResident() => "{$user->name} (Reporter)",
            $history->status === CommunityReport::STATUS_PENDING => ($report->reporter_name ?: 'Citizen Reporter').' (Reporter)',
            default => 'BFP Personnel',
        };

        return [
            'id' => (string) $history->history_id,
            'status' => $history->status,
            'notes' => $history->notes,
            'changed_at' => $history->created_at ? $history->created_at->format('Y-m-d H:i') : '',
            'changed_by' => $changedByName,
            'personnel' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'rank' => $rank,
                'employee_number' => $user->personnelDetails?->employee_number,
            ] : null,
        ];
    }
}
