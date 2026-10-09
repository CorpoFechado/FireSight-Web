<?php

use App\Enums\RiskLevel;
use App\Models\Barangay;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\RiskAssessment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        'alarm_level' => '3rd_alarm',
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

test('risk analytics provides mild, moderate, and high risk_level correctly', function () {
    $user = User::factory()->create(['role' => 'bfp_personnel']);
    $b1 = Barangay::firstOrCreate(
        ['barangay_name' => 'Barangay Mild', 'latitude' => 13.91, 'longitude' => 120.61]
    );
    $b2 = Barangay::firstOrCreate(
        ['barangay_name' => 'Barangay Moderate', 'latitude' => 13.92, 'longitude' => 120.62]
    );
    $b3 = Barangay::firstOrCreate(
        ['barangay_name' => 'Barangay High', 'latitude' => 13.93, 'longitude' => 120.63]
    );

    RiskAssessment::create([
        'barangay_id' => $b1->barangay_id,
        'date' => now()->toDateString(),
        'prediction_score' => 0.25,
        'risk_level' => RiskLevel::Mild,
    ]);

    RiskAssessment::create([
        'barangay_id' => $b2->barangay_id,
        'date' => now()->toDateString(),
        'prediction_score' => 0.55,
        'risk_level' => 'moderate',
    ]);

    RiskAssessment::create([
        'barangay_id' => $b3->barangay_id,
        'date' => now()->toDateString(),
        'prediction_score' => 0.85,
        'risk_level' => RiskLevel::High,
    ]);

    $this->actingAs($user)
        ->get(route('fireProne'))
        ->assertOk()
        ->assertInertia(function ($page) use ($b1, $b2, $b3) {
            $page->component('risk-analytics/index');

            $barangayRisk = collect($page->toArray()['props']['barangayRisk']);
            $mildItem = $barangayRisk->firstWhere('barangay_id', $b1->barangay_id);
            $modItem = $barangayRisk->firstWhere('barangay_id', $b2->barangay_id);
            $highItem = $barangayRisk->firstWhere('barangay_id', $b3->barangay_id);

            expect($mildItem['risk_level'])->toBe('mild')
                ->and($modItem['risk_level'])->toBe('moderate')
                ->and($highItem['risk_level'])->toBe('high');
        });
});

test('risk assessment casts risk_level to RiskLevel enum', function () {
    $assessment = new RiskAssessment;
    $assessment->risk_level = 'mild';
    expect($assessment->risk_level)->toBe(RiskLevel::Mild)
        ->and($assessment->risk_level->label())->toBe('Mild')
        ->and($assessment->risk_level->color())->toBe('#EAB308');

    $assessment->risk_level = RiskLevel::Moderate;
    expect($assessment->risk_level)->toBe(RiskLevel::Moderate)
        ->and($assessment->risk_level->label())->toBe('Moderate')
        ->and($assessment->risk_level->color())->toBe('#F97316');

    $assessment->risk_level = RiskLevel::High;
    expect($assessment->risk_level)->toBe(RiskLevel::High)
        ->and($assessment->risk_level->label())->toBe('High')
        ->and($assessment->risk_level->color())->toBe('#DC2626');
});

test('RiskLevel::fromScore derives correct level at boundaries', function () {
    // 0.0 - 1.0 scale boundaries
    expect(RiskLevel::fromScore(0.39))->toBe(RiskLevel::Mild)
        ->and(RiskLevel::fromScore(0.40))->toBe(RiskLevel::Moderate)
        ->and(RiskLevel::fromScore(0.69))->toBe(RiskLevel::Moderate)
        ->and(RiskLevel::fromScore(0.70))->toBe(RiskLevel::High);

    // Extreme points on 0.0 - 1.0 scale
    expect(RiskLevel::fromScore(0.0))->toBe(RiskLevel::Mild)
        ->and(RiskLevel::fromScore(0.3999))->toBe(RiskLevel::Mild)
        ->and(RiskLevel::fromScore(0.4001))->toBe(RiskLevel::Moderate)
        ->and(RiskLevel::fromScore(0.6999))->toBe(RiskLevel::Moderate)
        ->and(RiskLevel::fromScore(1.0))->toBe(RiskLevel::High);

    // 0 - 100 percentage scale boundaries
    expect(RiskLevel::fromScore(39))->toBe(RiskLevel::Mild)
        ->and(RiskLevel::fromScore(40))->toBe(RiskLevel::Moderate)
        ->and(RiskLevel::fromScore(69))->toBe(RiskLevel::Moderate)
        ->and(RiskLevel::fromScore(70))->toBe(RiskLevel::High);
});

test('migration simplifies risk_level to BFP scale and maps data correctly', function () {
    $migration = require database_path('migrations/2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php');

    // Run down() to restore previous schema
    $migration->down();

    $b = Barangay::firstOrCreate(
        ['barangay_name' => 'Migration Map Test', 'latitude' => 13.94, 'longitude' => 120.64]
    );

    // Insert records with old values
    DB::table('risk_assessment')->insert([
        ['barangay_id' => $b->barangay_id, 'date' => '2026-01-01', 'prediction_score' => 0.20, 'risk_level' => 'low'],
        ['barangay_id' => $b->barangay_id, 'date' => '2026-02-01', 'prediction_score' => 0.50, 'risk_level' => 'moderate'],
        ['barangay_id' => $b->barangay_id, 'date' => '2026-03-01', 'prediction_score' => 0.75, 'risk_level' => 'high'],
        ['barangay_id' => $b->barangay_id, 'date' => '2026-04-01', 'prediction_score' => 0.90, 'risk_level' => 'critical'],
    ]);

    // Run up()
    $migration->up();

    $rows = DB::table('risk_assessment')
        ->where('barangay_id', $b->barangay_id)
        ->orderBy('date')
        ->get();

    expect($rows[0]->risk_level)->toBe('mild')
        ->and($rows[1]->risk_level)->toBe('moderate')
        ->and($rows[2]->risk_level)->toBe('high')
        ->and($rows[3]->risk_level)->toBe('high');

    // Run down() to verify reverse mapping
    $migration->down();

    $downRows = DB::table('risk_assessment')
        ->where('barangay_id', $b->barangay_id)
        ->orderBy('date')
        ->get();

    expect($downRows[0]->risk_level)->toBe('low')
        ->and($downRows[1]->risk_level)->toBe('moderate')
        ->and($downRows[2]->risk_level)->toBe('high')
        ->and($downRows[3]->risk_level)->toBe('high');

    // Re-run up() so the test suite finishes with canonical schema
    $migration->up();
});
