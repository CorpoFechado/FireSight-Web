<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Support\Geo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    private const STATUSES = [
        CommunityReport::STATUS_PENDING,
        CommunityReport::STATUS_VERIFIED,
        CommunityReport::STATUS_DISPATCHED,
        CommunityReport::STATUS_RESOLVED,
        CommunityReport::STATUS_REJECTED,
    ];

    public function index(Request $request): Response
    {
        $status = $request->string('status')->lower()->value();
        $search = $request->string('search')->trim()->value();

        $reports = CommunityReport::query()
            ->with(['incidentRecord.barangay'])
            ->when(in_array($status, self::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('reporter_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhereHas('incidentRecord.barangay', fn ($q) => $q->where('barangay_name', 'like', "%{$search}%"));
                });
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (CommunityReport $report) => $this->toRow($report));

        return Inertia::render('incidents/index', [
            'reports' => $reports,
            'filters' => ['status' => $status ?: 'all', 'search' => $search],
            'totalCount' => CommunityReport::count(),
        ]);
    }

    public function show(CommunityReport $report): Response
    {
        $report->load([
            'incidentRecord.barangay',
            'linkedReports.incidentRecord.barangay',
            'linkedFromReports.incidentRecord.barangay',
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
            'linkedReports' => $linked,
            'barangays' => Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name']),
            'suggestedBarangayId' => $report->status === CommunityReport::STATUS_PENDING
                ? $this->suggestBarangayId($report)
                : null,
        ]);
    }

    /**
     * Pre-fill guess for the verify form — not authoritative, the admin can
     * always pick a different barangay. Tries an exact polygon match first
     * (point-in-polygon against `barangay.boundary`), and only falls back
     * to nearest-centroid distance if the report's coordinates don't land
     * inside any known boundary (e.g. GPS drift right at a border, or a
     * barangay that's missing boundary data).
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
            'barangay' => $report->incidentRecord?->barangay?->barangay_name,
            'type' => $report->incidentRecord ? ucfirst($report->incidentRecord->incident_type) : null,
            'severity' => $report->incidentRecord?->severity_level,
            'status' => $report->status,
            'dateTime' => $report->created_at->format('Y-m-d H:i'),
        ];
    }

    private function reference(CommunityReport $report): string
    {
        return sprintf('INC-%s-%04d', $report->created_at->format('Y'), $report->report_id);
    }
}
