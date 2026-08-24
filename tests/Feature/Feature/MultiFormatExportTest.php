<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

function usersExportActor(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    return $admin;
}

test('users xlsx export downloads a spreadsheet', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = usersExportActor();
    User::factory()->create(['name' => 'Zara']);

    $this->actingAs($admin)
        ->get(route('exports.users.xlsx'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $content = $this->actingAs($admin)->get(route('exports.users.xlsx'))->streamedContent();

    expect(strlen($content))->toBeGreaterThan(100)
        ->and(substr($content, 0, 2))->toBe('PK');
});

test('users xml export returns well-formed xml with every user', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = usersExportActor();
    User::factory()->create(['name' => 'Zara', 'email' => 'zara@example.com']);

    $response = $this->actingAs($admin)
        ->get(route('exports.users.xml'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse()
        ->and((string) $xml->getName())->toBe('users')
        ->and(count($xml->xpath('//user')))->toBe(2);
});

test('workspace summary pdf downloads a pdf document', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = usersExportActor();

    $response = $this->actingAs($admin)
        ->get(route('exports.summary.pdf'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/pdf');

    $content = $response->getContent();

    expect(substr($content, 0, 4))->toBe('%PDF');
});

test('export center lists the new format actions for permitted roles', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create();
    $manager->assignRole('Manager');

    $props = $this->actingAs($manager)
        ->get(route('exports.index'))
        ->assertOk()
        ->inertiaProps()['resources'];

    $keys = array_column($props, 'key');

    expect($keys)->toContain('users-csv')
        ->and($keys)->toContain('users-xlsx')
        ->and($keys)->toContain('users-xml')
        ->and($keys)->toContain('workspace-pdf');
});

test('guests are forbidden from every new export format', function () {
    $this->seed(RolePermissionSeeder::class);

    $guest = User::factory()->create();
    $guest->assignRole('Guest');

    $this->actingAs($guest)->get(route('exports.users.xlsx'))->assertForbidden();
    $this->actingAs($guest)->get(route('exports.users.xml'))->assertForbidden();
    $this->actingAs($guest)->get(route('exports.summary.pdf'))->assertForbidden();
});
