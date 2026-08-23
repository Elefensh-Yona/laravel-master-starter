<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/');
});

test('newly registered users start unverified when email verification is enabled', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Verification User',
        'email' => 'verification@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/');

    $user = User::query()->where('email', 'verification@example.com')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeFalse();
});
