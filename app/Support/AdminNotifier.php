<?php

namespace App\Support;

use App\Models\CommunityReport;
use App\Models\Notification;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Builds and fans out staff-facing notifications to every active BFP admin.
 *
 * Every public method accepts an optional `$at` timestamp so seeders can
 * backfill notifications using the original event times.
 */
class AdminNotifier
{
    /** Minimum AI confidence for a fire/smoke detection to be flagged as critical. */
    public const CRITICAL_AI_CONFIDENCE = 0.9;

    /** @var list<string> */
    public const CRITICAL_AI_LABELS = ['fire', 'smoke'];

    /** Reports within this radius of an active report are flagged as possible duplicates. */
    public const DUPLICATE_RADIUS_METERS = 300;

    /** Only active reports submitted within this many hours are considered for duplicates. */
    public const DUPLICATE_WINDOW_HOURS = 3;

    /** @var array<string, string> */
    private const STATUS_LABELS = [
        CommunityReport::STATUS_PENDING => 'Pending',
        CommunityReport::STATUS_ACCEPTED => 'Accepted',
        CommunityReport::STATUS_DISPATCHED => 'Dispatched',
        CommunityReport::STATUS_RESOLVED => 'Resolved',
        CommunityReport::STATUS_INVALID => 'Invalid',
    ];

    /**
     * A new community report came in. High-confidence AI detections are sent
     * as a single critical alert instead of a regular "new report" notice.
     */
    public function reportSubmitted(CommunityReport $report, ?CarbonInterface $at = null): void
    {
        if ($this->isCriticalAiDetection($report->ai_fire_label, $report->ai_fire_confidence)) {
            $this->criticalAiDetection($report, $at);

            return;
        }

        $this->send(
            'New Community Report',
            "Report #{$report->report_id} from {$this->reporterName($report)}{$this->locationSuffix($report)} is awaiting verification.",
            'incident_alert',
            null,
            $at,
        );
    }

    public function criticalAiDetection(CommunityReport $report, ?CarbonInterface $at = null): void
    {
        $confidence = (int) round(((float) $report->ai_fire_confidence) * 100);
        $label = $report->ai_fire_label === 'smoke' ? 'smoke' : 'fire';

        $this->send(
            'Critical: AI-Detected Fire',
            "AI detected {$label} with {$confidence}% confidence on Report #{$report->report_id}{$this->locationSuffix($report)}. Prioritize verification.",
            'incident_alert',
            null,
            $at,
        );
    }

    /**
     * @param  Collection<int, CommunityReport>  $nearbyReports
     */
    public function possibleDuplicate(CommunityReport $report, Collection $nearbyReports, ?CarbonInterface $at = null): void
    {
        if ($nearbyReports->isEmpty()) {
            return;
        }

        $reportList = $nearbyReports
            ->map(fn (CommunityReport $nearby) => "#{$nearby->report_id}")
            ->implode(', ');
        $noun = $nearbyReports->count() === 1 ? 'Report' : 'Reports';

        $this->send(
            'Possible Duplicate Report',
            "Report #{$report->report_id} was submitted near active {$noun} {$reportList}. Review and link them if they describe the same incident.",
            'incident_alert',
            null,
            $at,
        );
    }

    public function statusChanged(CommunityReport $report, string $status, ?User $actor, ?CarbonInterface $at = null): void
    {
        $label = self::STATUS_LABELS[$status] ?? ucfirst($status);
        $title = $status === CommunityReport::STATUS_INVALID ? 'Report Marked Invalid' : "Report {$label}";

        $this->send(
            $title,
            "{$this->actorName($actor)} marked Report #{$report->report_id}{$this->locationSuffix($report)} as {$label}.",
            'status_update',
            $actor?->id,
            $at,
        );
    }

    public function incidentDetailsUpdated(CommunityReport $report, ?User $actor, ?CarbonInterface $at = null): void
    {
        $this->send(
            'Incident Details Updated',
            "{$this->actorName($actor)} updated the incident assessment for Report #{$report->report_id}{$this->locationSuffix($report)}.",
            'system',
            $actor?->id,
            $at,
        );
    }

    public function isCriticalAiDetection(?string $label, ?float $confidence): bool
    {
        return $label !== null
            && $confidence !== null
            && in_array($label, self::CRITICAL_AI_LABELS, true)
            && $confidence >= self::CRITICAL_AI_CONFIDENCE;
    }

    /**
     * Active (pending/accepted/dispatched) reports submitted recently within
     * {@see self::DUPLICATE_RADIUS_METERS} of the given report.
     *
     * @return Collection<int, CommunityReport>
     */
    public function findNearbyActiveReports(CommunityReport $report): Collection
    {
        if ($report->latitude === null || $report->longitude === null) {
            return collect();
        }

        $latitude = (float) $report->latitude;
        $longitude = (float) $report->longitude;
        $latitudeDelta = self::DUPLICATE_RADIUS_METERS / 111320;
        $longitudeDelta = $latitudeDelta / max(cos(deg2rad($latitude)), 0.01);
        $reference = $report->created_at ?? now();

        return CommunityReport::query()
            ->whereKeyNot($report->getKey())
            ->whereIn('status', [
                CommunityReport::STATUS_PENDING,
                CommunityReport::STATUS_ACCEPTED,
                CommunityReport::STATUS_DISPATCHED,
            ])
            ->whereBetween('created_at', [$reference->copy()->subHours(self::DUPLICATE_WINDOW_HOURS), $reference])
            ->whereBetween('latitude', [$latitude - $latitudeDelta, $latitude + $latitudeDelta])
            ->whereBetween('longitude', [$longitude - $longitudeDelta, $longitude + $longitudeDelta])
            ->orderBy('report_id')
            ->get()
            ->filter(fn (CommunityReport $other) => Geo::distanceInMeters(
                $latitude,
                $longitude,
                (float) $other->latitude,
                (float) $other->longitude,
            ) <= self::DUPLICATE_RADIUS_METERS)
            ->values();
    }

    private function send(string $title, string $message, string $type, ?int $exceptUserId, ?CarbonInterface $at): void
    {
        $recipientIds = User::query()
            ->where('role', User::ROLE_BFP_ADMIN)
            ->where('status', User::STATUS_ACTIVE)
            ->when($exceptUserId !== null, fn ($query) => $query->whereKeyNot($exceptUserId))
            ->pluck('id');

        if ($recipientIds->isEmpty()) {
            return;
        }

        $createdAt = $at ?? now();

        Notification::insert($recipientIds->map(fn (int $id) => [
            'user_id' => $id,
            'title' => $title,
            'message' => $message,
            'notification_type' => $type,
            'is_read' => false,
            'created_at' => $createdAt,
        ])->all());
    }

    private function reporterName(CommunityReport $report): string
    {
        return $report->reporter_name ?: ($report->reporter?->name ?: 'a resident');
    }

    private function actorName(?User $actor): string
    {
        if (! $actor) {
            return 'System';
        }

        $rank = $actor->personnelDetails?->rank;

        return trim(($rank ? "{$rank} " : '').$actor->name) ?: 'BFP personnel';
    }

    private function locationSuffix(CommunityReport $report): string
    {
        $barangayName = $report->barangay?->barangay_name;

        return $barangayName ? " in {$barangayName}" : '';
    }
}
