<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the public root route renders the welcome component', function () {
    $response = $this->get(route('home'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('auth')
        );
});

test('the login route remains accessible', function () {
    $response = $this->get(route('login'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')
        );
});
