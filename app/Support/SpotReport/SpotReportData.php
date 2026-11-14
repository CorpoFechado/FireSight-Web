<?php

namespace App\Support\SpotReport;

use App\Http\Controllers\IncidentController;
use App\Models\CommunityReport;
use App\Support\IncidentTypeBreakdown;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Formatted, display-ready values for the BFP Spot Investigation Report (SIR).
 */
final class SpotReportData
{
    public const NOT_APPLICABLE = 'N/A';

    public function __construct(
        public readonly string $reference,
        public readonly string $memoDate,
        public readonly string $dtpo,
        public readonly string $involved,
        public readonly string $establishment,
        public readonly string $owner,
        public readonly string $occupant,
        public readonly string $fatalities,
        public readonly string $injured,
        public readonly string $estimatedDamage,
        public readonly string $timeFireStarted,
        public readonly string $timeFireOut,
        public readonly string $alarm,
        public readonly ?string $causeOfFire,
        public readonly ?string $notes,
    ) {}

    /**
     * Reasons the report cannot be exported yet (empty when it is ready).
     *
     * @return list<string>
     */
    public static function blockingReasons(CommunityReport $report): array
    {
        if ($report->status !== CommunityReport::STATUS_RESOLVED) {
            return ['Incident must be resolved'];
        }

        $record = $report->incidentRecord;

        if ($record === null) {
            return ['BFP assessment has not been recorded'];
        }

        return $record->missingSpotReportFields();
    }

    public static function fromReport(CommunityReport $report, ?CarbonInterface $exportedAt = null): self
    {
        $record = $report->incidentRecord;
        $timezone = config('app.timezone');
        $exportedAt = Carbon::instance($exportedAt ?? now())->setTimezone($timezone);

        $occurredAt = $record?->time_fire_started ?? $record?->incident_datetime ?? $report->created_at;
        $barangayName = $record?->barangay?->barangay_name ?? $report->barangay?->barangay_name;

        $place = $barangayName !== null
            ? sprintf('Brgy. %s, %s, %s', $barangayName, config('bfp.station.municipality'), config('bfp.station.province'))
            : sprintf('Outside %s jurisdiction (%s, %s)', config('bfp.station.municipality'), (float) $report->latitude, (float) $report->longitude);

        $dtpo = $occurredAt !== null
            ? sprintf('%s, %s at %s', self::formatDate($occurredAt), self::formatMilitaryTime($occurredAt), $place)
            : $place;

        $type = $record?->normalized_type;

        return new self(
            reference: IncidentController::reference($report),
            memoDate: self::formatDate($exportedAt),
            dtpo: $dtpo,
            involved: $type !== null
                ? (IncidentTypeBreakdown::TYPE_LABELS[$type] ?? ucwords(str_replace('_', ' ', $type)))
                : self::NOT_APPLICABLE,
            establishment: self::orNotApplicable($record?->establishment_name),
            owner: self::orNotApplicable($record?->owner_name),
            occupant: self::orNotApplicable($record?->occupant_name),
            fatalities: self::formatCount($record?->fatalities),
            injured: self::formatCount($record?->injuries),
            estimatedDamage: '₱ '.number_format((float) ($record?->estimated_damage ?? 0), 2),
            timeFireStarted: $record?->time_fire_started !== null ? self::formatMilitaryTime($record->time_fire_started) : self::NOT_APPLICABLE,
            timeFireOut: $record?->time_fire_out !== null ? self::formatMilitaryTime($record->time_fire_out) : self::NOT_APPLICABLE,
            alarm: $record?->alarm_level?->label() ?? self::NOT_APPLICABLE,
            causeOfFire: self::nullIfBlank($record?->cause_of_fire),
            notes: self::nullIfBlank($record?->notes),
        );
    }

    /**
     * Suggested download filename, e.g. `SIR-INC-2026-0042-20261114.docx`.
     */
    public function filename(string $extension = 'docx'): string
    {
        return sprintf('SIR-%s-%s.%s', $this->reference, now()->format('Ymd'), $extension);
    }

    /**
     * Paragraphs for the "Details of Investigation" section.
     *
     * @return list<string>
     */
    public function detailParagraphs(): array
    {
        $paragraphs = [];

        if ($this->causeOfFire !== null) {
            $paragraphs[] = 'Cause of fire: '.$this->causeOfFire;
        }

        if ($this->notes !== null) {
            foreach (preg_split('/\R/u', $this->notes) ?: [] as $line) {
                $paragraphs[] = $line;
            }
        }

        return $paragraphs === [] ? [self::NOT_APPLICABLE] : $paragraphs;
    }

    private static function formatDate(CarbonInterface $date): string
    {
        return Carbon::instance($date)->setTimezone(config('app.timezone'))->format('d F Y');
    }

    private static function formatMilitaryTime(CarbonInterface $date): string
    {
        return Carbon::instance($date)->setTimezone(config('app.timezone'))->format('Hi').'H';
    }

    private static function formatCount(?int $count): string
    {
        return $count !== null && $count > 0 ? (string) $count : 'None';
    }

    private static function orNotApplicable(?string $value): string
    {
        return self::nullIfBlank($value) ?? self::NOT_APPLICABLE;
    }

    private static function nullIfBlank(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
