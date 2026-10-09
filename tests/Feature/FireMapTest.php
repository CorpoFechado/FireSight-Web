<?php

use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Create a resolved CommunityReport with a linked IncidentRecord.
 *
 * @param  array<string, mixed>  $reportAttrs
 * @param  array<string, mixed>  $recordAttrs
 */
function makeResolved(array $reportAttrs = [], array $recordAttrs = []): CommunityReport
{
    $user = User::factory()->create(['role' => 'resident']);

    $createdAt = $reportAttrs['created_at'] ?? null;
    unset($reportAttrs['created_at']);

    $report = CommunityReport::create(array_merge([
        'user_id' => $user->id,
        'reporter_name' => 'Test Reporter',
        'contact_number' => '09001234567',
        'description' => 'Test fire report',
        'latitude' => 13.9354,
        'longitude' => 120.6560,
        'status' => CommunityReport::STATUS_RESOLVED,
    ], $reportAttrs));

    if ($createdAt !== null) {
        $report->forceFill(['created_at' => $createdAt])->save();
    }

    IncidentRecord::create(array_merge([
        'report_id' => $report->report_id,
        'incident_type' => 'residential_fire',
        'alarm_level' => '3rd_alarm',
        'incident_datetime' => now(),
    ], $recordAttrs));

    return $report;
}

// ─── Auth gate ───────────────────────────────────────────────────────────────

test('guests are redirected to login', function () {
    $this->get(route('map'))->assertRedirect(route('login'));
});

test('bfp_admin can access the map', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create())
        ->get(route('map'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('map/index'));
});

test('bfp_personnel can access the map', function () {
    $this->actingAs(User::factory()->bfpPersonnel()->create())
        ->get(route('map'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('map/index'));
});

// ─── Status filtering ─────────────────────────────────────────────────────────

test('only resolved reports appear on the map', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 1, 15));

    $resolved = makeResolved(['created_at' => now()]);

    foreach ([
        CommunityReport::STATUS_PENDING,
        CommunityReport::STATUS_ACCEPTED,
        CommunityReport::STATUS_DISPATCHED,
        CommunityReport::STATUS_INVALID,
    ] as $status) {
        $otherUser = User::factory()->create(['role' => 'resident']);
        CommunityReport::create([
            'user_id' => $otherUser->id,
            'reporter_name' => 'Other',
            'contact_number' => '09001234567',
            'description' => 'Other report',
            'latitude' => 13.9354,
            'longitude' => 120.6560,
            'status' => $status,
        ]);
    }

    $this->get(route('map', ['period' => 'this_year']))
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('incidents', 1)
                ->where('incidents.0.report_id', $resolved->report_id),
        );

    Date::setTestNow();
});

// ─── Period filtering ─────────────────────────────────────────────────────────

test('this_year period only includes incidents from current calendar year', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeResolved(['created_at' => Carbon::create(2026, 3, 1)]);
    makeResolved(['created_at' => Carbon::create(2025, 12, 31)]); // previous year — excluded

    $this->get(route('map', ['period' => 'this_year']))
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->has('incidents', 1),
        );

    Date::setTestNow();
});

test('default period is this_year when no period passed', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeResolved(['created_at' => Carbon::create(2026, 6, 1)]);  // in
    makeResolved(['created_at' => Carbon::create(2025, 12, 31)]); // out

    $this->get(route('map'))
        ->assertInertia(
            fn ($page) => $page
                ->component('map/index')
                ->where('filters.period', 'this_year')
                ->has('incidents', 1),
        );

    Date::setTestNow();
});

// ─── Type / alarm level filtering ───────────────────────────────────────────

test('incident_type filter returns only matching type', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeResolved(['created_at' => now()], ['incident_type' => 'residential_fire']);
    makeResolved(['created_at' => now()], ['incident_type' => 'vehicular_fire']);

    $this->get(route('map', ['incident_type' => 'residential_fire']))
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 1)
                ->where('incidents.0.type', 'residential_fire'),
        );

    Date::setTestNow();
});

test('alarm_level filter returns only matching alarm level', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeResolved(['created_at' => now()], ['alarm_level' => '5th_alarm']);
    makeResolved(['created_at' => now()], ['alarm_level' => '1st_alarm']);

    $this->get(route('map', ['alarm_level' => '5th_alarm']))
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 1)
                ->where('incidents.0.alarm_level', '5th_alarm'),
        );

    Date::setTestNow();
});

test('residential incidents in the last 3 months — key use-case', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 9, 20));

    // In range + right type
    makeResolved(['created_at' => Carbon::create(2026, 7, 15)], ['incident_type' => 'residential_fire']);
    makeResolved(['created_at' => Carbon::create(2026, 8, 1)], ['incident_type' => 'residential_fire']);

    // In range, wrong type
    makeResolved(['created_at' => Carbon::create(2026, 8, 1)], ['incident_type' => 'vehicular_fire']);

    // Out of range, right type
    makeResolved(['created_at' => Carbon::create(2026, 5, 1)], ['incident_type' => 'residential_fire']);

    $this->get(route('map', ['period' => 'last_3_months', 'incident_type' => 'residential_fire']))
        ->assertInertia(fn ($page) => $page->has('incidents', 2));

    Date::setTestNow();
});

test('type and alarm level combined with period', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeResolved(
        ['created_at' => now()],
        ['incident_type' => 'residential_fire', 'alarm_level' => '5th_alarm'],
    );

    makeResolved(
        ['created_at' => now()],
        ['incident_type' => 'residential_fire', 'alarm_level' => '1st_alarm'],
    );

    makeResolved(
        ['created_at' => now()],
        ['incident_type' => 'vehicular_fire', 'alarm_level' => '5th_alarm'],
    );

    $this->get(route('map', [
        'period' => 'this_year',
        'incident_type' => 'residential_fire',
        'alarm_level' => '5th_alarm',
    ]))->assertInertia(fn ($page) => $page->has('incidents', 1));

    Date::setTestNow();
});

test('invalid incident_type does not error and shows all types', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeResolved(['created_at' => now()], ['incident_type' => 'residential_fire']);
    makeResolved(['created_at' => now()], ['incident_type' => 'vehicular_fire']);

    $this->get(route('map', ['incident_type' => 'invalid_type']))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 2)
                ->where('filters.incident_type', 'all'),
        );

    Date::setTestNow();
});

test('invalid alarm_level does not error and shows all alarm levels', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeResolved(['created_at' => now()], ['alarm_level' => '5th_alarm']);
    makeResolved(['created_at' => now()], ['alarm_level' => '1st_alarm']);

    $this->get(route('map', ['alarm_level' => 'extreme']))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 2)
                ->where('filters.alarm_level', 'all'),
        );

    Date::setTestNow();
});

test('returned incident shape has all required fields', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    $report = makeResolved(
        ['created_at' => now(), 'latitude' => 13.9354, 'longitude' => 120.6560],
        ['incident_type' => 'residential_fire', 'alarm_level' => '3rd_alarm'],
    );

    $this->get(route('map'))
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 1)
                ->where('incidents.0.report_id', $report->report_id)
                ->where('incidents.0.type', 'residential_fire')
                ->where('incidents.0.typeLabel', 'Residential Fire')
                ->where('incidents.0.alarm_level', '3rd_alarm')
                ->where('incidents.0.latitude', 13.9354)
                ->where('incidents.0.longitude', 120.6560)
                ->has('incidents.0.dateTime')
                ->has('incidents.0.reference'),
        );

    Date::setTestNow();
});

test('filters prop echoes the active values', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    $this->get(route('map', [
        'period' => 'last_3_months',
        'incident_type' => 'residential_fire',
        'alarm_level' => '5th_alarm',
    ]))->assertInertia(
        fn ($page) => $page
            ->where('filters.period', 'last_3_months')
            ->where('filters.incident_type', 'residential_fire')
            ->where('filters.alarm_level', '5th_alarm'),
    );
});
