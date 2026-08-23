<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('admin can create a user and assign roles', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Sam Support',
            'email' => 'sam.support@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => ['Staff'],
        ])
        ->assertRedirect();

    $user = User::query()->where('email', 'sam.support@example.com')->firstOrFail();

    expect($user->name)->toBe('Sam Support')
        ->and($user->hasRole('Staff'))->toBeTrue();
});

test('admin can update user details', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $this->actingAs($admin)
        ->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('users.edit', $user));

    expect($user->fresh()->name)->toBe('Updated Name')
        ->and($user->fresh()->email)->toBe('updated@example.com');
});

test('admin can delete another user but not the current session user', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $targetUser = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $targetUser))
        ->assertRedirect(route('users.index'));

    expect(User::query()->whereKey($targetUser->id)->exists())->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('error');
});
