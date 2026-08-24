<?php

use App\Support\SystemRole;

test('system role constants resolve to the four canonical roles', function () {
    expect(SystemRole::names())->toBe([
        SystemRole::SUPER_ADMIN,
        SystemRole::MANAGER,
        SystemRole::STAFF,
        SystemRole::GUEST,
    ]);
});

test('guest system role carries zero permissions by contract', function () {
    $roles = [
        SystemRole::SUPER_ADMIN,
        SystemRole::MANAGER,
        SystemRole::STAFF,
        SystemRole::GUEST,
    ];

    expect($roles)->each->toBeString()
        ->and(array_count_values($roles))->each->toBe(1);
});
