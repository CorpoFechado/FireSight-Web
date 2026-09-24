<?php

use App\Models\Barangay;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->bfpAdmin()->create());
});

test('incidents index renders with pending status by default and new filter keys', function () {
    $response = $this->get(route('incidents.index'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('incidents/index')
            ->has('barangays')
            ->where('filters.status', 'pending')
            ->where('filters.date_from', '')
            ->where('filters.date_to', '')
            ->where('filters.barangay_id', ''),
    );
});

test('status filter can be set to all or other specific status', function () {
    $response = $this->get(route('incidents.index', ['status' => 'all']));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('filters.status', 'all'),
    );

    $responseAccepted = $this->get(route('incidents.index', ['status' => 'accepted']));
    $responseAccepted->assertOk();
    $responseAccepted->assertInertia(
        fn ($page) => $page->where('filters.status', 'accepted'),
    );
});

test('date_from filter is accepted and reflected in filters prop', function () {
    $response = $this->get(route('incidents.index', ['date_from' => '2025-01-01']));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('filters.date_from', '2025-01-01'),
    );
});

test('date_to filter is accepted and reflected in filters prop', function () {
    $response = $this->get(route('incidents.index', ['date_to' => '2025-12-31']));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('filters.date_to', '2025-12-31'),
    );
});

test('barangay_id filter is accepted and reflected in filters prop', function () {
    $barangay = Barangay::first();

    if (! $barangay) {
        $this->markTestSkipped('No barangays seeded.');
    }

    $response = $this->get(route('incidents.index', ['barangay_id' => $barangay->barangay_id]));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->where('filters.barangay_id', (string) $barangay->barangay_id),
    );
});

test('all existing filters still work alongside new ones', function () {
    $response = $this->get(route('incidents.index', [
        'status' => 'pending',
        'search' => 'test',
        'date_from' => '2024-01-01',
        'date_to' => '2024-12-31',
    ]));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.status', 'pending')
            ->where('filters.search', 'test')
            ->where('filters.date_from', '2024-01-01')
            ->where('filters.date_to', '2024-12-31'),
    );
});
