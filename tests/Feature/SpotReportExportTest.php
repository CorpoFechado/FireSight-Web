<?php

use App\Enums\AlarmLevel;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use App\Support\SpotReport\SpotReportData;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->staff = User::factory()->bfpAdmin()->create();
    $this->resident = User::factory()->create(['role' => 'resident']);

    $this->barangay = Barangay::firstOrCreate(
        ['barangay_id' => 101],
        ['barangay_name' => 'Matabungkay', 'centroid_lat' => 13.9, 'centroid_lng' => 120.6]
    );
});

/**
 * @param  array<string, mixed>  $recordOverrides
 */
function createSpotReportIncident(object $test, string $status = CommunityReport::STATUS_RESOLVED, array $recordOverrides = [], bool $withRecord = true): CommunityReport
{
    $report = CommunityReport::create([
        'user_id' => $test->resident->getKey(),
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09123456789',
        'description' => 'House fire',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'barangay_id' => $test->barangay->barangay_id,
        'status' => $status,
    ]);

    if ($withRecord) {
        IncidentRecord::create([
            'report_id' => $report->report_id,
            'barangay_id' => $test->barangay->barangay_id,
            'incident_datetime' => '2026-11-10 14:30:00',
            'incident_type' => 'residential_fire',
            'alarm_level' => AlarmLevel::FirstAlarm,
            'cause_of_fire' => 'Faulty electrical wiring',
            'injuries' => 2,
            'fatalities' => 0,
            'casualties' => 2,
            'estimated_damage' => 150000,
            'establishment_name' => 'Santos Residence',
            'owner_name' => 'Maria Santos',
            'occupant_name' => 'Pedro Santos',
            'time_fire_started' => '2026-11-10 14:30:00',
            'time_fire_out' => '2026-11-10 15:45:00',
            'notes' => "Fire originated in the kitchen.\nResponders contained it within an hour.",
            ...$recordOverrides,
        ]);
    }

    return $report;
}

function spotReportDocumentXml(string $content): string
{
    $path = tempnam(sys_get_temp_dir(), 'sir_test_');
    file_put_contents($path, $content);

    $zip = new ZipArchive;
    $zip->open($path);
    $xml = (string) $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($path);

    return $xml;
}

test('bfp staff can download the spot report of a resolved incident with a complete assessment', function () {
    $report = createSpotReportIncident($this);

    $response = $this->actingAs($this->staff)->get(route('incidents.spotReport', $report));

    $response->assertOk()
        ->assertDownload('SIR-INC-'.$report->created_at->format('Y').'-'.sprintf('%04d', $report->report_id).'-'.now()->format('Ymd').'.docx');

    $xml = spotReportDocumentXml($response->streamedContent());

    expect($xml)
        ->toContain('Spot Investigation Report (SIR)')
        ->toContain('Santos Residence')
        ->toContain('Maria Santos')
        ->toContain('Pedro Santos')
        ->toContain('10 November 2026, 1430H at Brgy. Matabungkay, Lian, Batangas')
        ->toContain('Residential Fire')
        ->toContain('₱ 150,000.00')
        ->toContain('1545H')
        ->toContain('1st Alarm')
        ->toContain('Cause of fire: Faulty electrical wiring')
        ->toContain('Fire originated in the kitchen.')
        ->toContain('Progress Investigation Report to follow.')
        ->toContain('Fire Arson Investigator')
        ->toContain('Chief, IIU');
});

test('export is blocked when the assessment is incomplete', function () {
    $report = createSpotReportIncident($this, recordOverrides: ['cause_of_fire' => null, 'owner_name' => '']);

    $this->actingAs($this->staff)
        ->from(route('incidents.show', $report))
        ->get(route('incidents.spotReport', $report))
        ->assertRedirect(route('incidents.show', $report))
        ->assertSessionHas('toast', fn (array $toast) => $toast['type'] === 'error'
            && str_contains($toast['message'], 'Cause of fire')
            && str_contains($toast['message'], 'Owner'));
});

test('vehicular fires can be exported without establishment, owner or occupant', function () {
    $report = createSpotReportIncident($this, recordOverrides: [
        'incident_type' => 'vehicular_fire',
        'establishment_name' => null,
        'owner_name' => null,
        'occupant_name' => null,
    ]);

    $response = $this->actingAs($this->staff)->get(route('incidents.spotReport', $report));

    $response->assertOk();
    expect(spotReportDocumentXml($response->streamedContent()))
        ->toContain('Vehicular Fire')
        ->toContain(SpotReportData::NOT_APPLICABLE);
});

test('export is blocked for incidents that are not resolved', function () {
    $report = createSpotReportIncident($this, CommunityReport::STATUS_DISPATCHED);

    $this->actingAs($this->staff)
        ->get(route('incidents.spotReport', $report))
        ->assertRedirect()
        ->assertSessionHas('toast', fn (array $toast) => str_contains($toast['message'], 'Incident must be resolved'));
});

test('export is blocked when no assessment record exists', function () {
    $report = createSpotReportIncident($this, withRecord: false);

    $this->actingAs($this->staff)
        ->get(route('incidents.spotReport', $report))
        ->assertRedirect()
        ->assertSessionHas('toast', fn (array $toast) => str_contains($toast['message'], 'BFP assessment has not been recorded'));
});

test('guests and residents cannot export spot reports', function () {
    $report = createSpotReportIncident($this);

    $this->get(route('incidents.spotReport', $report))->assertRedirect(route('login'));

    $residentResponse = $this->actingAs($this->resident)->get(route('incidents.spotReport', $report));
    expect($residentResponse->isOk())->toBeFalse();
});

test('incident show page exposes spot report readiness', function () {
    $ready = createSpotReportIncident($this);
    $incomplete = createSpotReportIncident($this, recordOverrides: ['alarm_level' => null]);

    $this->actingAs($this->staff)
        ->get(route('incidents.show', $ready))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('spotReport.ready', true)
            ->where('spotReport.missing', []));

    $this->actingAs($this->staff)
        ->get(route('incidents.show', $incomplete))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('spotReport.ready', false)
            ->where('spotReport.missing', ['Alarm level']));
});

test('spot report data formats values and falls back for missing data', function () {
    $report = createSpotReportIncident($this, recordOverrides: [
        'barangay_id' => null,
        'fatalities' => 0,
        'injuries' => 0,
        'establishment_name' => '  ',
    ]);
    $report->update(['barangay_id' => null]);
    $report->load(['incidentRecord.barangay', 'barangay']);

    $data = SpotReportData::fromReport($report, Carbon::parse('2026-11-14 09:00:00', 'Asia/Manila'));

    expect($data->memoDate)->toBe('14 November 2026')
        ->and($data->dtpo)->toBe('10 November 2026, 1430H at Outside Lian jurisdiction (13.95, 120.65)')
        ->and($data->fatalities)->toBe('None')
        ->and($data->injured)->toBe('None')
        ->and($data->establishment)->toBe(SpotReportData::NOT_APPLICABLE)
        ->and($data->estimatedDamage)->toBe('₱ 150,000.00')
        ->and($data->timeFireStarted)->toBe('1430H')
        ->and($data->timeFireOut)->toBe('1545H')
        ->and($data->alarm)->toBe('1st Alarm')
        ->and($data->detailParagraphs())->toBe([
            'Cause of fire: Faulty electrical wiring',
            'Fire originated in the kitchen.',
            'Responders contained it within an hour.',
        ]);
});
