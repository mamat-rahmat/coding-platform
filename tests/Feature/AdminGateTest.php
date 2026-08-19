<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin user passes admin gate', function () {
    $admin = User::factory()->admin()->create();

    expect(Gate::forUser($admin)->allows('admin'))->toBeTrue();
});

test('non-admin user fails admin gate', function () {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('admin'))->toBeFalse();
});

test('admin gate is registered', function () {
    expect(Gate::has('admin'))->toBeTrue();
});
