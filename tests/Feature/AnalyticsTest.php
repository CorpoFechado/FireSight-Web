<?php

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::create(2026, 6, 15, 12, 0, 0));
    $this->actingAs(User::factory()->bfpAdmin()->create());
});

test('guests are redirected to login', function () {
    auth()->logout();
    $this->get(route('analytics'))->assertRedirect(route('login'));
});

test('non-admin users cannot access analytics', function () {
    $this->actingAs(User::factory()->create(['role' => 'resident']))
        ->get(route('analytics'))
        ->assertForbidden();
});

test('bfp admin can view the prevention analytics overview', function () {
    $response = $this->get(route('analytics'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('analytics/index')
            ->has('barangayOverview')
            ->where('summary.total', 0)
            ->where('summary.unclassified', 0)
            ->where('summary.unassigned', 0)
            ->where('selectedBarangay', null)
            ->where('supportingIncidents', null)
            ->missing('responseTimeTrend')
            ->has('typeOptions', 6)
            ->has('monthlyTrend')
            ->has('monthlyTrend.0.month')
            ->has('monthlyTrend.0.incidents')
            ->missing('monthlyTrend.0.resolved')
            ->has('incidentsByAlarmLevel')
            ->where('incidentsByAlarmLevel.0.level', 'verification')
            ->where('incidentsByAlarmLevel.0.label', 'Verification')
            ->where('incidentsByAlarmLevel.0.color', '#A9B8D0')
            ->has('filters')
            ->where('filters.period', 'this_year')
            ->where('filters.incident_type', '')
            ->where('filters.barangay_id', null)
            ->missing('filters.trend_year')
            ->has('periodLabel')
    );
});

test('barangay overview reflects the shared period', function () {
    $b1 = Barangay::firstOrCreate(
        ['barangay_id' => 901],
        ['barangay_name' => 'Barangay Alpha', 'centroid_lat' => 13.9, 'centroid_lng' => 120.6]
    );
    $b2 = Barangay::firstOrCreate(
        ['barangay_id' => 902],
        ['barangay_name' => 'Barangay Beta', 'centroid_lat' => 13.9, 'centroid_lng' => 120.6]
    );

    $resident = User::factory()->create(['role' => 'resident']);

    $now = Carbon::create(2026, 6, 15, 12, 0, 0);
    Carbon::setTestNow($now);

    // Create 3 incidents in Barangay Alpha this month (June 2026)
    for ($i = 0; $i < 3; $i++) {
        $report = CommunityReport::create([
            'user_id' => $resident->getKey(),
            'reporter_name' => 'Reporter',
            'contact_number' => '09123456789',
            'description' => 'Test report',
            'latitude' => 13.9,
            'longitude' => 120.6,
            'status' => CommunityReport::STATUS_RESOLVED,
        ]);
        IncidentRecord::create([
            'report_id' => $report->report_id,
            'barangay_id' => $b1->barangay_id,
            'incident_datetime' => Carbon::create(2026, 6, 10, 10, 0, 0),
            'incident_type' => 'residential_fire',
            'alarm_level' => '3rd_alarm',
        ]);
    }

    // Create 1 incident in Barangay Beta this month (June 2026)
    $reportBeta = CommunityReport::create([
        'user_id' => $resident->getKey(),
        'reporter_name' => 'Reporter',
        'contact_number' => '09123456789',
        'description' => 'Test report beta',
        'latitude' => 13.9,
        'longitude' => 120.6,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);
    IncidentRecord::create([
        'report_id' => $reportBeta->report_id,
        'barangay_id' => $b2->barangay_id,
        'incident_datetime' => Carbon::create(2026, 6, 12, 10, 0, 0),
        'incident_type' => 'rubbish_fire',
        'alarm_level' => '1st_alarm',
    ]);

    // Create 5 incidents in Barangay Beta from last year (2025) - should NOT show when filtering "this_month"
    for ($i = 0; $i < 5; $i++) {
        $reportOld = CommunityReport::create([
            'user_id' => $resident->getKey(),
            'reporter_name' => 'Reporter',
            'contact_number' => '09123456789',
            'description' => 'Old report',
            'latitude' => 13.9,
            'longitude' => 120.6,
            'status' => CommunityReport::STATUS_RESOLVED,
        ]);
        IncidentRecord::create([
            'report_id' => $reportOld->report_id,
            'barangay_id' => $b2->barangay_id,
            'incident_datetime' => Carbon::create(2025, 6, 10, 10, 0, 0),
            'incident_type' => 'residential_fire',
            'alarm_level' => '3rd_alarm',
        ]);
    }

    // Query with "this_month" snapshot period
    $response = $this->get(route('analytics', ['period' => 'this_month']));
    $response->assertOk();
    $response->assertInertia(function ($page) {
        $page->where('barangayOverview.0.barangay_name', 'Barangay Alpha')
            ->where('barangayOverview.0.count', 3)
            ->where('barangayOverview.1.barangay_name', 'Barangay Beta')
            ->where('barangayOverview.1.count', 1)
            ->has('monthlyTrend', 1)
            ->where('monthlyTrend.0.incidents', 4);
    });

    Carbon::setTestNow();
});

/**
 * @param  array<string, mixed>  $attributes
 */
function createAnalyticsIncident(?Barangay $barangay, array $attributes = []): IncidentRecord
{
    $report = CommunityReport::create([
        'user_id' => auth()->id(),
        'reporter_name' => 'Analytics reporter',
        'contact_number' => '09123456789',
        'description' => 'Assessed incident',
        'latitude' => 13.9,
        'longitude' => 120.6,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    return IncidentRecord::create(array_merge([
        'report_id' => $report->report_id,
        'barangay_id' => $barangay?->barangay_id,
        'incident_datetime' => '2026-06-10 12:00:00',
        'incident_type' => 'residential_fire',
        'alarm_level' => '1st_alarm',
    ], $attributes));
}

test('overview accounts for unclassified and unassigned incidents and zero count barangays', function () {
    $alpha = Barangay::create(['barangay_name' => 'Alpha']);
    $beta = Barangay::create(['barangay_name' => 'Beta']);
    $empty = Barangay::create(['barangay_name' => 'Empty']);
    createAnalyticsIncident($alpha);
    createAnalyticsIncident($alpha, ['incident_type' => 'structural']);
    createAnalyticsIncident($beta, ['incident_type' => null]);
    createAnalyticsIncident(null, ['incident_type' => null]);

    $this->get(route('analytics'))->assertOk()->assertInertia(fn ($page) => $page
        ->where('summary.total', 4)
        ->where('summary.unclassified', 2)
        ->where('summary.unassigned', 1)
        ->has('barangayOverview', 3)
        ->where('barangayOverview.0.count', 2)
        ->where('barangayOverview.0.leading_types', ['Residential Fire'])
        ->where('barangayOverview.1.barangay_id', $beta->barangay_id)
        ->where('barangayOverview.1.leading_types', [])
        ->where('barangayOverview.2.barangay_id', $empty->barangay_id)
        ->where('barangayOverview.2.count', 0)
        ->where('monthlyTrend.5.incidents', 4));
});

test('leading types include ties and exclude unclassified counts', function () {
    $beta = Barangay::create(['barangay_name' => 'Beta']);
    $alpha = Barangay::create(['barangay_name' => 'Alpha']);
    foreach ([$alpha, $beta] as $barangay) {
        createAnalyticsIncident($barangay);
        createAnalyticsIncident($barangay, ['incident_type' => 'rubbish_fire']);
        createAnalyticsIncident($barangay, ['incident_type' => null]);
        createAnalyticsIncident($barangay, ['incident_type' => null]);
    }

    $this->get(route('analytics'))->assertInertia(fn ($page) => $page
        ->where('barangayOverview.0.barangay_name', 'Alpha')
        ->where('barangayOverview.1.barangay_name', 'Beta')
        ->where('barangayOverview.0.leading_types', ['Residential Fire', 'Rubbish Fire']));
});

test('period presets bound every monthly bucket', function (string $period, int $expected) {
    $barangay = Barangay::create(['barangay_name' => 'Alpha']);
    foreach (['2026-03-31', '2026-04-01', '2026-05-31', '2026-06-01', '2026-07-01'] as $date) {
        createAnalyticsIncident($barangay, ['incident_datetime' => $date.' 12:00:00']);
    }

    $this->get(route('analytics', ['period' => $period]))->assertInertia(fn ($page) => $page
        ->where('summary.total', $expected)
        ->where('monthlyTrend', fn ($months) => collect($months)->sum('incidents') === $expected));
})->with([
    'this month' => ['this_month', 1],
    'last three months' => ['last_3_months', 3],
    'year to date' => ['this_year', 4],
]);

test('custom ranges include boundary days and zero months across years', function () {
    $barangay = Barangay::create(['barangay_name' => 'Alpha']);
    foreach (['2025-12-14 23:59:59', '2025-12-15 00:00:00', '2026-02-03 23:59:59', '2026-02-04 00:00:00'] as $date) {
        createAnalyticsIncident($barangay, ['incident_datetime' => $date]);
    }

    $this->get(route('analytics', ['period' => 'custom', 'date_from' => '2025-12-15', 'date_to' => '2026-02-03']))
        ->assertInertia(fn ($page) => $page
            ->where('summary.total', 2)
            ->where('dateRange.start', '2025-12-15')
            ->where('dateRange.end', '2026-02-03')
            ->where('periodLabel', 'Dec 15, 2025 – Feb 3, 2026')
            ->where('monthlyTrend', [
                ['month' => 'Dec 2025', 'incidents' => 1],
                ['month' => 'Jan 2026', 'incidents' => 0],
                ['month' => 'Feb 2026', 'incidents' => 1],
            ]));
});

test('canonical type filters include legacy types consistently', function (string $legacy, string $canonical) {
    $alpha = Barangay::create(['barangay_name' => 'Alpha']);
    $beta = Barangay::create(['barangay_name' => 'Beta']);
    createAnalyticsIncident($alpha, ['incident_type' => $legacy, 'injuries' => 2]);
    createAnalyticsIncident($alpha, ['incident_type' => $canonical, 'injuries' => 3]);
    createAnalyticsIncident($beta, ['incident_type' => $legacy]);
    createAnalyticsIncident($alpha, ['incident_type' => null, 'injuries' => 10]);
    createAnalyticsIncident($alpha, ['incident_type' => 'storage_fire', 'injuries' => 20]);

    $this->get(route('analytics', ['incident_type' => $canonical, 'barangay_id' => $alpha->barangay_id]))
        ->assertOk()->assertInertia(fn ($page) => $page
        ->where('summary.total', 3)
        ->where('summary.unclassified', 0)
        ->where('barangayOverview.0.count', 2)
        ->where('selectedBarangay.count', 2)
        ->where('selectedBarangay.injuries', 5)
        ->has('selectedBarangay.types', 1)
        ->where('selectedBarangay.types.0.type', $canonical)
        ->where('selectedBarangay.types.0.count', 2)
        ->where('monthlyTrend.5.incidents', 2)
        ->where('incidentsByAlarmLevel', fn ($rows) => collect($rows)->sum('count') === 2)
        ->where('supportingIncidents.total', 2));
})->with([
    'structural' => ['structural', 'residential_fire'],
    'grass' => ['grass', 'rubbish_fire'],
    'electrical' => ['electrical', 'rubbish_fire'],
    'vehicular' => ['vehicular', 'vehicular_fire'],
    'other' => ['other', 'others'],
]);

test('selecting and closing a barangay preserves the overall comparison', function () {
    $alpha = Barangay::create(['barangay_name' => 'Alpha']);
    $beta = Barangay::create(['barangay_name' => 'Beta']);
    createAnalyticsIncident($alpha, ['fatalities' => 1]);
    createAnalyticsIncident($beta);
    createAnalyticsIncident($beta);

    $this->get(route('analytics', ['barangay_id' => $alpha->barangay_id]))->assertInertia(fn ($page) => $page
        ->where('summary.total', 3)
        ->where('barangayOverview.0.barangay_id', $beta->barangay_id)
        ->where('barangayOverview.0.count', 2)
        ->where('selectedBarangay.barangay_id', $alpha->barangay_id)
        ->where('selectedBarangay.fatalities', 1)
        ->where('monthlyTrend.5.incidents', 1)
        ->where('supportingIncidents.total', 1));

    $this->get(route('analytics'))->assertInertia(fn ($page) => $page
        ->where('summary.total', 3)
        ->where('selectedBarangay', null)
        ->where('supportingIncidents', null)
        ->where('monthlyTrend.5.incidents', 3));
});

test('a selected barangay remains available when filters match no incidents', function () {
    $barangay = Barangay::create(['barangay_name' => 'Alpha']);
    createAnalyticsIncident($barangay);

    $this->get(route('analytics', ['barangay_id' => $barangay->barangay_id, 'incident_type' => 'commercial_fire']))
        ->assertInertia(fn ($page) => $page
            ->where('selectedBarangay.barangay_id', $barangay->barangay_id)
            ->where('selectedBarangay.count', 0)
            ->where('selectedBarangay.types', [])
            ->where('selectedBarangay.estimated_damage', null)
            ->where('selectedBarangay.damage_recorded', 0)
            ->where('supportingIncidents.total', 0)
            ->where('monthlyTrend.5.incidents', 0));
});

test('damage totals distinguish missing assessments from recorded zero', function (array $values, ?float $total, int $recorded) {
    $barangay = Barangay::create(['barangay_name' => 'Alpha']);
    foreach ($values as $value) {
        createAnalyticsIncident($barangay, ['estimated_damage' => $value]);
    }

    $this->get(route('analytics', ['barangay_id' => $barangay->barangay_id]))
        ->assertInertia(fn ($page) => $page
            ->where('selectedBarangay.count', count($values))
            ->where('selectedBarangay.estimated_damage', fn ($value) => $total === null ? $value === null : (float) $value === $total)
            ->where('selectedBarangay.damage_recorded', $recorded));
})->with([
    'missing assessments' => [[null, null], null, 0],
    'recorded zero' => [[0, null], 0.0, 1],
    'partial coverage' => [[1000.25, 2000.5, null], 3000.75, 2],
]);

test('supporting incidents use recorded dates and normalized types with stable pagination', function () {
    $barangay = Barangay::create(['barangay_name' => 'Alpha']);
    $ids = [];
    for ($index = 0; $index < 12; $index++) {
        $incident = createAnalyticsIncident($barangay, ['incident_type' => 'structural', 'cause_of_fire' => 'Recorded assessment']);
        $incident->report->forceFill(['created_at' => '2025-01-01 12:00:00'])->save();
        $ids[] = $incident->incident_id;
    }
    createAnalyticsIncident($barangay, ['incident_datetime' => '2025-06-10 12:00:00']);
    createAnalyticsIncident($barangay, ['incident_type' => 'rubbish_fire']);

    $filters = ['period' => 'this_month', 'incident_type' => 'residential_fire', 'barangay_id' => $barangay->barangay_id];
    $this->get(route('analytics', $filters))->assertInertia(fn ($page) => $page
        ->where('selectedBarangay.count', 12)
        ->where('supportingIncidents.total', 12)
        ->has('supportingIncidents.data', 10)
        ->where('supportingIncidents.data.0.incident_id', $ids[11])
        ->where('supportingIncidents.data.0.reference', sprintf('INC-2025-%04d', IncidentRecord::find($ids[11])->report_id))
        ->where('supportingIncidents.data.0.type', 'Residential Fire')
        ->where('supportingIncidents.data.0.cause', 'Recorded assessment')
        ->where('supportingIncidents.next_page_url', fn ($url) => str_contains($url, 'incident_page=2') && str_contains($url, 'incident_type=residential_fire') && str_contains($url, 'period=this_month')));

    $this->get(route('analytics', [...$filters, 'incident_page' => 2]))->assertInertia(fn ($page) => $page
        ->where('supportingIncidents.current_page', 2)
        ->has('supportingIncidents.data', 2)
        ->where('supportingIncidents.data.0.incident_id', $ids[1])
        ->where('supportingIncidents.data.1.incident_id', $ids[0]));
});

test('invalid analytics query inputs return validation errors', function (array $query, string $field) {
    $this->from(route('analytics'))->get(route('analytics', $query))
        ->assertRedirect(route('analytics'))->assertSessionHasErrors($field);
})->with([
    'period' => [['period' => 'unknown'], 'period'],
    'type' => [['incident_type' => 'unknown'], 'incident_type'],
    'array type' => [['incident_type' => ['residential_fire']], 'incident_type'],
    'barangay' => [['barangay_id' => 999999], 'barangay_id'],
    'page' => [['incident_page' => -1], 'incident_page'],
    'supporting view' => [['supporting_view' => 'unknown'], 'supporting_view'],
    'supporting type' => [['supporting_type' => 'unknown'], 'supporting_type'],
    'supporting alarm' => [['supporting_alarm_level' => 'unknown'], 'supporting_alarm_level'],
    'missing custom dates' => [['period' => 'custom'], 'date_from'],
    'malformed date' => [['period' => 'custom', 'date_from' => '2026-02-30', 'date_to' => '2026-06-15'], 'date_from'],
    'reversed range' => [['period' => 'custom', 'date_from' => '2026-06-15', 'date_to' => '2026-06-01'], 'date_to'],
]);

test('supporting filters narrow the selected barangay records while retaining the analytics context', function () {
    $alpha = Barangay::create(['barangay_name' => 'Alpha']);
    $beta = Barangay::create(['barangay_name' => 'Beta']);
    createAnalyticsIncident($alpha);
    $match = createAnalyticsIncident($alpha, ['incident_type' => 'structural', 'alarm_level' => '2nd_alarm']);
    createAnalyticsIncident($alpha, ['incident_type' => 'rubbish_fire', 'alarm_level' => '2nd_alarm']);
    createAnalyticsIncident($alpha, ['incident_type' => null, 'alarm_level' => null]);
    createAnalyticsIncident($beta, ['alarm_level' => '2nd_alarm']);
    createAnalyticsIncident(null, ['alarm_level' => '2nd_alarm']);

    $this->get(route('analytics', [
        'barangay_id' => $alpha->barangay_id,
        'supporting_type' => 'residential_fire',
        'supporting_alarm_level' => '2nd_alarm',
    ]))->assertInertia(fn ($page) => $page
        ->where('summary.total', 6)
        ->where('selectedBarangay.count', 4)
        ->where('monthlyTrend.5.incidents', 4)
        ->where('incidentsByAlarmLevel', fn ($rows) => collect($rows)->sum('count') === 3)
        ->where('supportingIncidents.total', 1)
        ->where('supportingIncidents.data.0.incident_id', $match->incident_id)
        ->where('supportingIncidents.data.0.type', 'Residential Fire')
        ->where('supportingIncidents.data.0.alarm_level', '2nd Alarm')
        ->where('filters.supporting_type', 'residential_fire')
        ->where('filters.supporting_alarm_level', '2nd_alarm'));
});

test('alarm supporting records are available across all barangays including unassigned incidents', function () {
    $alpha = Barangay::create(['barangay_name' => 'Alpha']);
    $beta = Barangay::create(['barangay_name' => 'Beta']);
    createAnalyticsIncident($alpha);
    createAnalyticsIncident($alpha, ['alarm_level' => '2nd_alarm']);
    createAnalyticsIncident($beta, ['incident_type' => 'rubbish_fire', 'alarm_level' => '2nd_alarm']);
    createAnalyticsIncident(null, ['alarm_level' => '2nd_alarm']);

    $this->get(route('analytics', ['supporting_view' => 'alarm', 'supporting_alarm_level' => '2nd_alarm']))
        ->assertInertia(fn ($page) => $page
            ->where('summary.total', 4)
            ->where('summary.unassigned', 1)
            ->where('selectedBarangay', null)
            ->where('supportingIncidents.total', 3)
            ->where('supportingIncidents.data', fn ($rows) => collect($rows)->every(fn ($row) => $row['alarm_level'] === '2nd Alarm')));
});

test('supporting records can isolate unclassified incidents awaiting alarm assessment', function () {
    $barangay = Barangay::create(['barangay_name' => 'Alpha']);
    $match = createAnalyticsIncident($barangay, ['incident_type' => null, 'alarm_level' => null]);
    createAnalyticsIncident($barangay, ['incident_type' => null]);
    createAnalyticsIncident($barangay, ['alarm_level' => null]);

    $this->get(route('analytics', [
        'supporting_view' => 'alarm',
        'supporting_type' => 'unclassified',
        'supporting_alarm_level' => 'unassessed',
    ]))->assertInertia(fn ($page) => $page
        ->where('summary.total', 3)
        ->where('supportingIncidents.total', 1)
        ->where('supportingIncidents.data.0.incident_id', $match->incident_id)
        ->where('supportingIncidents.data.0.type', 'Unclassified')
        ->where('supportingIncidents.data.0.alarm_level', 'Awaiting assessment'));
});

test('supporting filters respect the shared date type and barangay filters', function () {
    $alpha = Barangay::create(['barangay_name' => 'Alpha']);
    $beta = Barangay::create(['barangay_name' => 'Beta']);
    createAnalyticsIncident($alpha, ['incident_type' => 'structural']);
    createAnalyticsIncident($alpha, ['incident_type' => 'commercial_fire']);
    createAnalyticsIncident($alpha, ['incident_datetime' => '2026-05-01 12:00:00']);
    createAnalyticsIncident($beta);

    $filters = [
        'period' => 'this_month',
        'incident_type' => 'residential_fire',
        'barangay_id' => $alpha->barangay_id,
        'supporting_view' => 'alarm',
        'supporting_alarm_level' => '1st_alarm',
    ];
    $this->get(route('analytics', $filters))->assertInertia(fn ($page) => $page
        ->where('summary.total', 2)
        ->where('selectedBarangay.count', 1)
        ->where('supportingIncidents.total', 1));

    $this->get(route('analytics', [...$filters, 'supporting_type' => 'commercial_fire']))
        ->assertInertia(fn ($page) => $page
            ->where('selectedBarangay.count', 1)
            ->where('supportingIncidents.total', 0));
});

test('alarm evidence pagination retains its supporting filters', function () {
    $barangay = Barangay::create(['barangay_name' => 'Alpha']);
    for ($index = 0; $index < 12; $index++) {
        createAnalyticsIncident($barangay, ['incident_type' => 'structural', 'alarm_level' => '2nd_alarm']);
    }
    createAnalyticsIncident($barangay, ['alarm_level' => '1st_alarm']);
    createAnalyticsIncident($barangay, ['incident_type' => 'rubbish_fire', 'alarm_level' => '2nd_alarm']);

    $filters = ['supporting_view' => 'alarm', 'supporting_type' => 'residential_fire', 'supporting_alarm_level' => '2nd_alarm'];
    $this->get(route('analytics', $filters))->assertInertia(fn ($page) => $page
        ->where('supportingIncidents.total', 12)
        ->has('supportingIncidents.data', 10)
        ->where('supportingIncidents.next_page_url', fn ($url) => str_contains($url, 'supporting_view=alarm') && str_contains($url, 'supporting_type=residential_fire') && str_contains($url, 'supporting_alarm_level=2nd_alarm') && str_contains($url, 'incident_page=2')));

    $this->get(route('analytics', [...$filters, 'incident_page' => 2]))
        ->assertInertia(fn ($page) => $page
            ->where('supportingIncidents.total', 12)
            ->where('supportingIncidents.current_page', 2)
            ->has('supportingIncidents.data', 2));
});
