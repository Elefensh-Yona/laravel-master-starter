<?php

use App\Models\User;
use App\Support\Locales;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\App;

test('users default to the application locale', function () {
    $user = User::factory()->create();

    expect($user->locale)->toBe('en')
        ->and($user->effectiveLocale())->toBe('en');
});

test('effective locale falls back when the preference is unsupported', function () {
    $user = User::factory()->create(['locale' => 'xx_INVALID']);

    expect($user->effectiveLocale())->toBe(config('app.locale'));
});

test('profile update persists a supported locale', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'locale' => 'en',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->locale)->toBe('en');
});

test('profile update rejects unsupported locales', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'locale' => 'klingon',
        ])
        ->assertSessionHasErrors('locale');

    expect($user->fresh()->locale)->toBe('en');
});

test('the locale middleware applies the user preference to the app', function () {
    Locales::all();

    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk();

    expect(App::getLocale())->toBe('en');
});

test('shared inertia props expose translations and the available locales', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $response = $this->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();

    $props = $response->inertiaProps();

    expect($props['translations'])->toHaveKey('common')
        ->and($props['availableLocales'])->toBe(Locales::all());
});
