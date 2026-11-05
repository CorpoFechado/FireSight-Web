<?php

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

function createFireMapIncident(array $reportAttrs = [], array $recordAttrs = []): CommunityReport
{
    $user = User::factory()->create(['role' => 'resident']);

    $createdAt = $reportAttrs['created_at'] ?? now();
    unset($reportAttrs['created_at']);

    $report = CommunityReport::create(array_merge([
        'user_id' => $user->getKey(),
        'reporter_name' => 'Reporter '.fake()->name(),
        'contact_number' => '09001234567',
        'description' => 'Fire incident test report',
        'latitude' => 14.0392,
        'longitude' => 120.6389,
        'status' => CommunityReport::STATUS_RESOLVED,
    ], $reportAttrs));

    $report->forceFill(['created_at' => $createdAt])->save();

    IncidentRecord::create(array_merge([
        'report_id' => $report->report_id,
        'incident_type' => 'residential_fire',
        'alarm_level' => '1st_alarm',
        'incident_datetime' => now(),
    ], $recordAttrs));

    return $report;
}

beforeEach(function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());
    Date::setTestNow(Carbon::create(2026, 6, 15));
});

test('without barangay_id all resolved incidents are returned', function () {
    $b1 = Barangay::create(['barangay_name' => 'Barangay 1']);
    $b2 = Barangay::create(['barangay_name' => 'Barangay 2']);

    createFireMapIncident(['barangay_id' => $b1->barangay_id], ['barangay_id' => $b1->barangay_id]);
    createFireMapIncident(['barangay_id' => $b2->barangay_id], ['barangay_id' => $b2->barangay_id]);

    $this->get(route('map'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('incidents', 2)
                ->where('filters.barangay_id', '')
        );
});

test('with barangay_id only incidents in that barangay are returned', function () {
    $b1 = Barangay::create(['barangay_name' => 'Barangay Alpha']);
    $b2 = Barangay::create(['barangay_name' => 'Barangay Beta']);

    $inc1 = createFireMapIncident(
        ['barangay_id' => $b1->barangay_id, 'created_at' => now()->subHours(3)],
        ['barangay_id' => $b1->barangay_id]
    );
    $inc2 = createFireMapIncident(
        ['barangay_id' => null, 'created_at' => now()->subHours(2)],
        ['barangay_id' => $b1->barangay_id]
    ); // incident_record is source of truth
    $inc3 = createFireMapIncident(
        ['barangay_id' => $b1->barangay_id, 'created_at' => now()->subHours(1)],
        ['barangay_id' => null]
    ); // community_report fallback
    createFireMapIncident(['barangay_id' => $b2->barangay_id], ['barangay_id' => $b2->barangay_id]);

    $this->get(route('map', ['barangay_id' => $b1->barangay_id]))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('incidents', 3)
                ->where('filters.barangay_id', (string) $b1->barangay_id)
                ->where('incidents.0.report_id', $inc3->report_id)
                ->where('incidents.1.report_id', $inc2->report_id)
                ->where('incidents.2.report_id', $inc1->report_id)
        );
});

test('barangay filter combines with incident_type and alarm_level using AND logic', function () {
    $b1 = Barangay::create(['barangay_name' => 'Barangay Target']);
    $b2 = Barangay::create(['barangay_name' => 'Barangay Other']);

    // Matches barangay, type, and alarm
    $match = createFireMapIncident(
        ['barangay_id' => $b1->barangay_id],
        ['barangay_id' => $b1->barangay_id, 'incident_type' => 'commercial_fire', 'alarm_level' => '2nd_alarm']
    );

    // Matches barangay, but different type
    createFireMapIncident(
        ['barangay_id' => $b1->barangay_id],
        ['barangay_id' => $b1->barangay_id, 'incident_type' => 'residential_fire', 'alarm_level' => '2nd_alarm']
    );

    // Matches barangay, but different alarm level
    createFireMapIncident(
        ['barangay_id' => $b1->barangay_id],
        ['barangay_id' => $b1->barangay_id, 'incident_type' => 'commercial_fire', 'alarm_level' => '1st_alarm']
    );

    // Matches type and alarm, but in different barangay
    createFireMapIncident(
        ['barangay_id' => $b2->barangay_id],
        ['barangay_id' => $b2->barangay_id, 'incident_type' => 'commercial_fire', 'alarm_level' => '2nd_alarm']
    );

    $this->get(route('map', [
        'barangay_id' => $b1->barangay_id,
        'incident_type' => 'commercial_fire',
        'alarm_level' => '2nd_alarm',
    ]))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('incidents', 1)
                ->where('filters.barangay_id', (string) $b1->barangay_id)
                ->where('filters.incident_type', 'commercial_fire')
                ->where('filters.alarm_level', '2nd_alarm')
                ->where('incidents.0.report_id', $match->report_id)
        );
});

test('invalid or nonexistent barangay_id falls back to all and filters.barangay_id is empty string', function () {
    $b1 = Barangay::create(['barangay_name' => 'Barangay One']);
    $b2 = Barangay::create(['barangay_name' => 'Barangay Two']);

    createFireMapIncident(['barangay_id' => $b1->barangay_id], ['barangay_id' => $b1->barangay_id]);
    createFireMapIncident(['barangay_id' => $b2->barangay_id], ['barangay_id' => $b2->barangay_id]);

    // Nonexistent integer ID
    $this->get(route('map', ['barangay_id' => 999999]))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('incidents', 2)
                ->where('filters.barangay_id', '')
        );

    // Non-integer string ID
    $this->get(route('map', ['barangay_id' => 'not-a-number']))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('incidents', 2)
                ->where('filters.barangay_id', '')
        );
});

test('barangays prop is present and sorted by name', function () {
    Barangay::create(['barangay_name' => 'Zamboanga']);
    Barangay::create(['barangay_name' => 'Arayat']);
    Barangay::create(['barangay_name' => 'Bulacan']);

    $this->get(route('map'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('barangays')
                ->where('barangays', function ($barangays) {
                    $names = collect($barangays)->pluck('barangay_name')->all();
                    $sorted = collect($names)->sort()->values()->all();

                    return $names === $sorted;
                })
        );
});
