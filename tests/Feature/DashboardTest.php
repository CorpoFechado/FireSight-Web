<?php

use App\Models\CommunityReport;
use App\Models\ReportStatusHistory;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated bfp admin can visit the dashboard with mobile-matched kpis', function () {
    $user = User::factory()->bfpAdmin()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('kpis.activeIncidents')
        ->has('kpis.resolvedToday')
        ->has('kpis.resolvedThisMonth')
        ->has('kpis.pendingVerification')
        ->has('monthlyTrend', 7)
        ->has('monthlyTrend.0.month')
        ->has('monthlyTrend.0.incidents')
        ->missing('monthlyTrend.0.resolved')
    );
});

test('dashboard kpis accurately count active, pending, resolved today, and resolved this month', function () {
    $user = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->create();
    $this->actingAs($user);

    $base = [
        'user_id' => $resident->id,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09123456789',
        'description' => 'Fire sighting',
        'latitude' => 13.95,
        'longitude' => 120.65,
    ];

    // Active incidents (accepted or dispatched)
    CommunityReport::create(array_merge($base, ['status' => CommunityReport::STATUS_ACCEPTED]));
    CommunityReport::create(array_merge($base, ['status' => CommunityReport::STATUS_DISPATCHED]));

    // Pending verification
    CommunityReport::create(array_merge($base, ['status' => CommunityReport::STATUS_PENDING]));

    // Resolved today
    $resolvedToday = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_RESOLVED,
        'created_at' => now(),
        'updated_at' => now(),
    ]));
    ReportStatusHistory::create([
        'report_id' => $resolvedToday->report_id,
        'status' => CommunityReport::STATUS_RESOLVED,
        'created_at' => now(),
    ]);

    // Resolved earlier this month
    $resolvedMonth = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_RESOLVED,
        'created_at' => now()->startOfMonth()->addDays(2),
        'updated_at' => now()->startOfMonth()->addDays(2),
    ]));
    ReportStatusHistory::create([
        'report_id' => $resolvedMonth->report_id,
        'status' => CommunityReport::STATUS_RESOLVED,
        'created_at' => now()->startOfMonth()->addDays(2),
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('kpis.activeIncidents', fn ($count) => $count >= 2)
        ->where('kpis.pendingVerification', fn ($count) => $count >= 1)
        ->where('kpis.resolvedToday', fn ($count) => $count >= 1)
        ->where('kpis.resolvedThisMonth', fn ($count) => $count >= 2)
    );
});

test('dashboard kpis calculate comparisons for active, pending, resolved today, and resolved this month', function () {
    $user = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->create();
    $this->actingAs($user);

    $base = [
        'user_id' => $resident->id,
        'reporter_name' => 'Juan Dela Cruz',
        'contact_number' => '09123456789',
        'description' => 'Fire sighting',
        'latitude' => 13.95,
        'longitude' => 120.65,
    ];

    // Pending report created 3 hours ago -> "Oldest waiting 3h"
    $pending = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_PENDING,
    ]));
    $pending->timestamps = false;
    $pending->created_at = now()->subHours(3);
    $pending->save();

    // Active report created and accepted today
    $activeToday = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_ACCEPTED,
        'created_at' => now(),
    ]));
    ReportStatusHistory::create([
        'report_id' => $activeToday->report_id,
        'status' => CommunityReport::STATUS_ACCEPTED,
        'created_at' => now(),
    ]);

    // Active report from 2 days ago that was accepted 2 days ago
    $activeOld = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_ACCEPTED,
    ]));
    $activeOld->timestamps = false;
    $activeOld->created_at = now()->subDays(2);
    $activeOld->updated_at = now()->subDays(2);
    $activeOld->save();
    ReportStatusHistory::create([
        'report_id' => $activeOld->report_id,
        'status' => CommunityReport::STATUS_ACCEPTED,
        'created_at' => now()->subDays(2),
    ]);

    // Resolved today: 2 reports
    $resolved1 = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_RESOLVED,
    ]));
    ReportStatusHistory::create([
        'report_id' => $resolved1->report_id,
        'status' => CommunityReport::STATUS_RESOLVED,
        'created_at' => now(),
    ]);
    $resolved2 = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_RESOLVED,
    ]));
    ReportStatusHistory::create([
        'report_id' => $resolved2->report_id,
        'status' => CommunityReport::STATUS_RESOLVED,
        'created_at' => now(),
    ]);

    // Resolved yesterday: 1 report (so +1 from yesterday)
    $resolvedYest = CommunityReport::create(array_merge($base, [
        'status' => CommunityReport::STATUS_RESOLVED,
    ]));
    $resolvedYest->timestamps = false;
    $resolvedYest->created_at = now()->subDay();
    $resolvedYest->updated_at = now()->subDay();
    $resolvedYest->save();
    ReportStatusHistory::create([
        'report_id' => $resolvedYest->report_id,
        'status' => CommunityReport::STATUS_RESOLVED,
        'created_at' => now()->subDay(),
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('kpis.pendingComparison', 'Oldest waiting 3h')
        ->where('kpis.activeComparison', '+1 since yesterday')
        ->where('kpis.resolvedTodayComparison', '+1 from yesterday')
        ->where('kpis.resolvedThisMonthComparison', fn ($comp) => str_ends_with($comp, 'vs same period last month'))
    );
});

test('dashboard kpis handle edge cases when pending is empty or less than 1 hour', function () {
    $user = User::factory()->bfpAdmin()->create();
    $resident = User::factory()->create();
    $this->actingAs($user);

    // Empty dashboard
    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('kpis.pendingComparison', 'None waiting')
        ->where('kpis.activeComparison', '0 since yesterday')
        ->where('kpis.resolvedTodayComparison', '0 from yesterday')
        ->where('kpis.resolvedThisMonthComparison', '0 vs same period last month')
    );

    // Pending report submitted 20 minutes ago (<1h)
    $report = CommunityReport::create([
        'user_id' => $resident->id,
        'reporter_name' => 'Test User',
        'contact_number' => '09123456789',
        'description' => 'Test smoke',
        'latitude' => 13.95,
        'longitude' => 120.65,
        'status' => CommunityReport::STATUS_PENDING,
    ]);
    $report->timestamps = false;
    $report->created_at = now()->subMinutes(20);
    $report->save();

    $response2 = $this->get(route('dashboard'));
    $response2->assertOk();
    $response2->assertInertia(fn ($page) => $page
        ->where('kpis.pendingComparison', 'Oldest waiting <1h')
    );
});
