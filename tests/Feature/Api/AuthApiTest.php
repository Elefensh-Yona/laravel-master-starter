<?php

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Sanctum\PersonalAccessToken;

test('api login returns a bearer token and the current users rbac context', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create([
        'email' => 'api-admin@example.com',
        'password' => 'password',
    ]);
    $user->assignRole('Super Admin');

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'api-admin@example.com',
        'password' => 'password',
        'device_name' => 'iphone-15',
    ]);

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'roles',
                'permissions',
            ],
        ]);

    expect(PersonalAccessToken::query()->count())->toBe(1);
    expect(ActivityLog::query()->where('event', 'auth.api-login')->exists())->toBeTrue();
});

test('api me returns the authenticated user with roles and permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Manager');

    $token = $user->createToken('postman')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me')
        ->assertSuccessful()
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.roles.0', 'Manager');
});

test('api logout revokes the current token', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $token = $user->createToken('android')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout')
        ->assertSuccessful()
        ->assertJsonPath('message', 'Logged out successfully.');

    expect(PersonalAccessToken::query()->count())->toBe(0);
    expect(ActivityLog::query()->where('event', 'auth.api-logout')->exists())->toBeTrue();
});

test('api login rejects invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'wrong@example.com',
        'password' => 'password',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'android',
    ])->assertStatus(422);
});
