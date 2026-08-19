<?php

use App\Models\User;

test('guests are redirected from playground to login', function () {
    $response = $this->get(route('playground.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can access playground', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('playground.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('starterCode'));
});

test('playground provides starter code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('playground.index'));

    $response->assertInertia(
        fn ($page) => $page
            ->has('starterCode')
            ->where('starterCode', fn ($value) => is_string($value) && str_contains($value, 'print(')),
    );
});
