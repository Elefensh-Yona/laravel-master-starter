<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('admin api user listing returns paginated users', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    User::factory()->count(3)->create()->each(
        fn (User $user) => $user->assignRole('Staff'),
    );

    $token = $admin->createToken('postman')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/users')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'email_verified_at',
                    'created_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'filters' => [
                    'search',
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
            ],
        ]);
});

test('member cannot access the admin users api endpoint', function () {
    $this->seed(RolePermissionSeeder::class);

    $member = User::factory()->create();
    $member->assignRole('Staff');

    $token = $member->createToken('android')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/users')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden.');
});

test('api endpoints require sanctum authentication', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});
