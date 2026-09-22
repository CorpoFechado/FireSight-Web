<?php

use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Create a completed CommunityReport with a linked IncidentRecord.
 * community_report.user_id is NOT NULL, so we always supply a resident.
 *
 * @param  array<string, mixed>  $reportAttrs
 * @param  array<string, mixed>  $recordAttrs
 */
function makeCompleted(array $reportAttrs = [], array $recordAttrs = []): CommunityReport
{
    $user = User::factory()->create(['role' => 'resident']);

    // Extract created_at before passing to create() since it's not fillable.
    $createdAt = $reportAttrs['created_at'] ?? null;
    unset($reportAttrs['created_at']);

    $report = CommunityReport::create(array_merge([
        'user_id' => $user->id,
        'reporter_name' => 'Test Reporter',
        'contact_number' => '09001234567',
        'description' => 'Test fire report',
        'latitude' => 13.9354,
        'longitude' => 120.6560,
        'status' => CommunityReport::STATUS_COMPLETED,
    ], $reportAttrs));

    if ($createdAt !== null) {
        $report->forceFill(['created_at' => $createdAt])->save();
    }

    IncidentRecord::create(array_merge([
        'report_id' => $report->report_id,
        'incident_type' => 'electrical',
        'severity_level' => 'high',
        'data_time' => now(),
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

test('only completed reports appear on the map', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 1, 15));

    // Create one completed and one of each other status
    $completed = makeCompleted(['created_at' => now()]);

    foreach ([
        CommunityReport::STATUS_PENDING,
        CommunityReport::STATUS_VERIFIED,
        CommunityReport::STATUS_DISPATCHED,
        CommunityReport::STATUS_RESOLVED,
        CommunityReport::STATUS_REJECTED,
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
                ->where('incidents.0.report_id', $completed->report_id),
        );

    Date::setTestNow();
});

// ─── Period filtering ─────────────────────────────────────────────────────────

test('default period is this_year and excludes reports from previous year', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeCompleted(['created_at' => Carbon::create(2026, 3, 1)]);
    makeCompleted(['created_at' => Carbon::create(2025, 12, 31)]); // previous year — excluded

    $this->get(route('map')) // no period param → defaults to this_year
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 1)
                ->where('filters.period', 'this_year'),
        );

    Date::setTestNow();
});

test('unknown period falls back to this_year', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    $this->get(route('map', ['period' => 'invalid_period']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('filters.period', 'this_year'));
});

test('this_month includes only reports created this month', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 15));

    makeCompleted(['created_at' => Carbon::create(2026, 6, 1)]);  // in
    makeCompleted(['created_at' => Carbon::create(2026, 5, 31)]); // out

    $this->get(route('map', ['period' => 'this_month']))
        ->assertInertia(fn ($page) => $page->has('incidents', 1));

    Date::setTestNow();
});

test('last_3_months covers the right span', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 15));
    // last_3_months = start of April to end of June (3 months back from current month)
    makeCompleted(['created_at' => Carbon::create(2026, 4, 1)]);  // in
    makeCompleted(['created_at' => Carbon::create(2026, 6, 15)]); // in
    makeCompleted(['created_at' => Carbon::create(2026, 3, 31)]); // out (before April)

    $this->get(route('map', ['period' => 'last_3_months']))
        ->assertInertia(fn ($page) => $page->has('incidents', 2));

    Date::setTestNow();
});

test('custom date_from and date_to are inclusive and exclude outside reports', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    makeCompleted(['created_at' => Carbon::create(2026, 3, 1)]);  // included
    makeCompleted(['created_at' => Carbon::create(2026, 3, 31)]); // included
    makeCompleted(['created_at' => Carbon::create(2026, 2, 28)]); // excluded
    makeCompleted(['created_at' => Carbon::create(2026, 4, 1)]);  // excluded

    $this->get(route('map', [
        'period' => 'custom',
        'date_from' => '2026-03-01',
        'date_to' => '2026-03-31',
    ]))->assertInertia(fn ($page) => $page->has('incidents', 2));
});

// ─── Type / severity filtering ────────────────────────────────────────────────

test('incident_type filter returns only matching type', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeCompleted(['created_at' => now()], ['incident_type' => 'electrical']);
    makeCompleted(['created_at' => now()], ['incident_type' => 'structural']);

    $this->get(route('map', ['incident_type' => 'electrical']))
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 1)
                ->where('incidents.0.type', 'electrical'),
        );

    Date::setTestNow();
});

test('severity_level filter returns only matching severity', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeCompleted(['created_at' => now()], ['severity_level' => 'critical']);
    makeCompleted(['created_at' => now()], ['severity_level' => 'low']);

    $this->get(route('map', ['severity_level' => 'critical']))
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 1)
                ->where('incidents.0.severity', 'critical'),
        );

    Date::setTestNow();
});

test('electrical incidents in the last 3 months — the key use-case', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 9, 20));

    // In range + right type
    makeCompleted(['created_at' => Carbon::create(2026, 7, 15)], ['incident_type' => 'electrical']);
    makeCompleted(['created_at' => Carbon::create(2026, 8, 1)], ['incident_type' => 'electrical']);

    // In range, wrong type
    makeCompleted(['created_at' => Carbon::create(2026, 8, 1)], ['incident_type' => 'structural']);

    // Out of range, right type
    makeCompleted(['created_at' => Carbon::create(2026, 5, 1)], ['incident_type' => 'electrical']);

    $this->get(route('map', ['period' => 'last_3_months', 'incident_type' => 'electrical']))
        ->assertInertia(fn ($page) => $page->has('incidents', 2));

    Date::setTestNow();
});

test('type and severity combined with period', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    // Match: in range + right type + right severity
    makeCompleted(
        ['created_at' => now()],
        ['incident_type' => 'electrical', 'severity_level' => 'critical'],
    );

    // Wrong severity
    makeCompleted(
        ['created_at' => now()],
        ['incident_type' => 'electrical', 'severity_level' => 'low'],
    );

    // Wrong type
    makeCompleted(
        ['created_at' => now()],
        ['incident_type' => 'structural', 'severity_level' => 'critical'],
    );

    $this->get(route('map', [
        'period' => 'this_year',
        'incident_type' => 'electrical',
        'severity_level' => 'critical',
    ]))->assertInertia(fn ($page) => $page->has('incidents', 1));

    Date::setTestNow();
});

// ─── Invalid filter values ────────────────────────────────────────────────────

test('invalid incident_type does not error and shows all types', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeCompleted(['created_at' => now()], ['incident_type' => 'electrical']);
    makeCompleted(['created_at' => now()], ['incident_type' => 'structural']);

    $this->get(route('map', ['incident_type' => 'nuke']))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 2)
                ->where('filters.incident_type', 'all'),
        );

    Date::setTestNow();
});

test('invalid severity_level does not error and shows all severities', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    makeCompleted(['created_at' => now()], ['severity_level' => 'critical']);
    makeCompleted(['created_at' => now()], ['severity_level' => 'low']);

    $this->get(route('map', ['severity_level' => 'extreme']))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 2)
                ->where('filters.severity_level', 'all'),
        );

    Date::setTestNow();
});

test('invalid custom dates do not throw and fall back gracefully', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    $this->get(route('map', [
        'period' => 'custom',
        'date_from' => 'not-a-date',
        'date_to' => 'also-bad',
    ]))->assertOk();
});

// ─── Incident shape and filters prop ─────────────────────────────────────────

test('returned incident shape has all required fields', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    Date::setTestNow(Carbon::create(2026, 6, 1));

    $report = makeCompleted(
        ['created_at' => now(), 'latitude' => 13.9354, 'longitude' => 120.6560],
        ['incident_type' => 'electrical', 'severity_level' => 'high'],
    );

    $this->get(route('map'))
        ->assertInertia(
            fn ($page) => $page
                ->has('incidents', 1)
                ->where('incidents.0.report_id', $report->report_id)
                ->where('incidents.0.type', 'electrical')
                ->where('incidents.0.typeLabel', 'Electrical Fire')
                ->where('incidents.0.severity', 'high')
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
        'incident_type' => 'electrical',
        'severity_level' => 'critical',
    ]))->assertInertia(
        fn ($page) => $page
            ->where('filters.period', 'last_3_months')
            ->where('filters.incident_type', 'electrical')
            ->where('filters.severity_level', 'critical'),
    );
});

test('periodLabel prop is present', function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());

    $this->get(route('map'))
        ->assertInertia(fn ($page) => $page->has('periodLabel'));
});
