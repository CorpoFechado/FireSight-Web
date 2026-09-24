<?php

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
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

test('bfp admin can view analytics with barangays with most incidents', function () {
    $response = $this->get(route('analytics'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('analytics/index')
            ->has('barangaysWithMostIncidents')
            ->missing('responseTimeTrend')
            ->has('incidentsByType')
            ->has('monthlyTrend')
            ->has('incidentsBySeverity')
            ->has('filters')
            ->has('periodLabel')
    );
});

test('barangays with most incidents reflects snapshot period', function () {
    $b1 = Barangay::firstOrCreate(
        ['barangay_id' => 901],
        ['barangay_name' => 'Barangay Alpha', 'latitude' => 13.9, 'longitude' => 120.6]
    );
    $b2 = Barangay::firstOrCreate(
        ['barangay_id' => 902],
        ['barangay_name' => 'Barangay Beta', 'latitude' => 13.9, 'longitude' => 120.6]
    );

    $resident = User::factory()->create(['role' => 'resident']);

    $now = Carbon::create(2026, 6, 15, 12, 0, 0);
    Carbon::setTestNow($now);

    // Create 3 incidents in Barangay Alpha this month (June 2026)
    for ($i = 0; $i < 3; $i++) {
        $report = CommunityReport::create([
            'user_id' => $resident->id,
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
            'severity_level' => 'high',
        ]);
    }

    // Create 1 incident in Barangay Beta this month (June 2026)
    $reportBeta = CommunityReport::create([
        'user_id' => $resident->id,
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
        'severity_level' => 'low',
    ]);

    // Create 5 incidents in Barangay Beta from last year (2025) - should NOT show when filtering "this_month"
    for ($i = 0; $i < 5; $i++) {
        $reportOld = CommunityReport::create([
            'user_id' => $resident->id,
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
            'severity_level' => 'high',
        ]);
    }

    // Query with "this_month" snapshot period
    $response = $this->get(route('analytics', ['period' => 'this_month']));
    $response->assertOk();
    $response->assertInertia(function ($page) {
        $page->where('barangaysWithMostIncidents.0.barangay_name', 'Barangay Alpha')
            ->where('barangaysWithMostIncidents.0.count', 3)
            ->where('barangaysWithMostIncidents.1.barangay_name', 'Barangay Beta')
            ->where('barangaysWithMostIncidents.1.count', 1);
    });

    Carbon::setTestNow();
});
