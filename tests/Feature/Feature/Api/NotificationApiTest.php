<?php

use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\SystemMessageNotification;
use Database\Seeders\RolePermissionSeeder;

test('notification api lists and reads notifications', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');
    $user->notify(new SystemMessageNotification(
        title: 'API notice',
        message: 'Open the mobile client.',
        actionUrl: '/dashboard',
        actionLabel: 'Open dashboard',
    ));

    $token = $user->createToken('ios')->plainTextToken;
    $notification = $user->notifications()->latest()->firstOrFail();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/notifications?read=unread')
        ->assertOk()
        ->assertJsonPath('meta.filters.read', 'unread')
        ->assertJsonPath('meta.unread_count', 1)
        ->assertJsonPath('meta.pagination.current_page', 1)
        ->assertJsonPath('data.0.title', 'API notice');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/notifications/{$notification->id}/read")
        ->assertOk()
        ->assertJsonPath('message', 'Notification marked as read.')
        ->assertJsonPath('notification.read_at', fn ($value) => filled($value));

    expect(ActivityLog::query()->where('event', 'notifications.read')->exists())->toBeTrue();
});

test('notification api can mark all entries as read', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $user->notify(new SystemMessageNotification(title: 'One', message: 'First'));
    $user->notify(new SystemMessageNotification(title: 'Two', message: 'Second'));

    $token = $user->createToken('android')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('unread_count', 0);

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notification api enforces notification permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Guest');

    $token = $user->createToken('postman')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/notifications')
        ->assertForbidden();
});
