<?php

use App\Models\User;

function admin(): User
{
    return User::factory()->admin()->create();
}

function unapprovedUser(): User
{
    return User::factory()->unapproved()->create();
}

test('admin can approve a user', function () {
    $user = unapprovedUser();

    $this->actingAs(admin())
        ->patch(route('admin.users.approve', $user->id))
        ->assertRedirect();

    expect($user->fresh()->is_approved)->toBeTrue();
});

test('admin can reject a user', function () {
    $user = User::factory()->create(['is_approved' => true]);

    $this->actingAs(admin())
        ->patch(route('admin.users.reject', $user->id))
        ->assertRedirect();

    expect($user->fresh()->is_approved)->toBeFalse();
});

test('non-admin cannot approve users', function () {
    $user = unapprovedUser();

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.users.approve', $user->id))
        ->assertForbidden();

    expect($user->fresh()->is_approved)->toBeFalse();
});

test('non-admin cannot reject users', function () {
    $user = User::factory()->create(['is_approved' => true]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.users.reject', $user->id))
        ->assertForbidden();

    expect($user->fresh()->is_approved)->toBeTrue();
});
