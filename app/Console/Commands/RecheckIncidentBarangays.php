<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\IncidentRecord;
use App\Support\Geo;
use Illuminate\Console\Command;

class RecheckIncidentBarangays extends Command
{
    protected $signature = 'barangay:recheck-incidents {--fix : Actually update mismatched records instead of just listing them}';

    protected $description = 'Compares each incident_record.barangay_id against a fresh point-in-polygon lookup on its report coordinates, and optionally corrects mismatches.';

    public function handle(): int
    {
        $boundaries = Barangay::whereNotNull('boundary')->get();

        $incidents = IncidentRecord::with(['report', 'barangay'])->get();

        $mismatches = 0;

        foreach ($incidents as $incident) {
            $report = $incident->report;

            if (! $report) {
                continue;
            }

            $lat = (float) $report->latitude;
            $lng = (float) $report->longitude;

            $correct = $boundaries->first(fn (Barangay $b) => Geo::pointInPolygon($lat, $lng, $b->boundary));

            if (! $correct) {
                continue; // point falls outside every polygon — skip, don't guess
            }

            if ($correct->barangay_id !== $incident->barangay_id) {
                $mismatches++;
                $this->line(sprintf(
                    'Incident #%d (report #%d): stored "%s" -> polygon says "%s"',
                    $incident->incident_id,
                    $report->report_id,
                    $incident->barangay?->barangay_name ?? '—',
                    $correct->barangay_name,
                ));

                if ($this->option('fix')) {
                    $incident->update(['barangay_id' => $correct->barangay_id]);
                }
            }
        }

        if ($mismatches === 0) {
            $this->info('No mismatches found.');
        } elseif ($this->option('fix')) {
            $this->info("Fixed {$mismatches} mismatched record(s).");
        } else {
            $this->warn("{$mismatches} mismatch(es) found. Re-run with --fix to apply.");
        }

        return self::SUCCESS;
    }
}