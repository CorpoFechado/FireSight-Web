<?php

use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\RiskAssessment;
use App\Models\User;

test('guests are redirected to login from risk analytics', function () {
    $this->get(route('fireProne'))->assertRedirect(route('login'));
});

test('authenticated bfp user can access risk analytics page with ranking data', function () {
    $user = User::factory()->create(['role' => 'bfp_personnel']);
    $barangay = Barangay::firstOrCreate(
        ['barangay_name' => 'Barangay Test', 'latitude' => 13.9, 'longitude' => 120.6]
    );

    $reporter = User::factory()->create(['role' => 'resident']);
    $report = CommunityReport::create([
        'user_id' => $reporter->id,
        'reporter_name' => 'Test Reporter',
        'contact_number' => '09001234567',
        'description' => 'Test fire report',
        'latitude' => 13.9354,
        'longitude' => 120.6560,
        'status' => CommunityReport::STATUS_RESOLVED,
    ]);

    IncidentRecord::create([
        'report_id' => $report->report_id,
        'barangay_id' => $barangay->barangay_id,
        'incident_type' => 'residential_fire',
        'severity_level' => 'high',
        'incident_datetime' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('fireProne'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('risk-analytics/index')
            ->has('barangayRisk')
            ->has('ranking')
            ->has('incidentFrequency')
        );
});

test('risk analytics provides critical risk_level correctly', function () {
    $user = User::factory()->create(['role' => 'bfp_personnel']);
    $barangay = Barangay::firstOrCreate(
        ['barangay_name' => 'Barangay Risk Test', 'latitude' => 13.9, 'longitude' => 120.6]
    );

    RiskAssessment::create([
        'barangay_id' => $barangay->barangay_id,
        'date' => now()->toDateString(),
        'prediction_score' => 0.95,
        'risk_level' => 'critical',
    ]);

    $this->actingAs($user)
        ->get(route('fireProne'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('risk-analytics/index')
            ->where('barangayRisk.0.risk_level', 'critical')
        );
});

test('risk assessment normalizes legacy severe to critical', function () {
    $assessment = new RiskAssessment;
    $assessment->risk_level = 'severe';
    expect($assessment->risk_level)->toBe('critical');

    $assessment->setRawAttributes(['risk_level' => 'severe']);
    expect($assessment->risk_level)->toBe('critical');
});
