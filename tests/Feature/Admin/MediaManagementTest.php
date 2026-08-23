<?php

use App\Models\Media;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can view the media library', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    Media::factory()->count(2)->create([
        'uploaded_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('media.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Media/Index')
            ->has('media.data', 2),
        );
});

test('manager can upload media but cannot delete it', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create();
    $manager->assignRole('Manager');

    $this->actingAs($manager)
        ->post(route('media.store'), [
            'collection' => 'contracts',
            'file' => UploadedFile::fake()->create('contract.pdf', 120, 'application/pdf'),
        ])
        ->assertRedirect(route('media.index'));

    $media = Media::query()->latest()->firstOrFail();

    Storage::disk('local')->assertExists($media->path);

    expect($media->collection)->toBe('contracts')
        ->and($media->uploaded_by)->toBe($manager->id);

    $this->actingAs($manager)
        ->delete(route('media.destroy', $media))
        ->assertForbidden();
});

test('admin can download and delete media', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $media = Media::factory()->create([
        'uploaded_by' => $admin->id,
        'disk' => 'local',
        'path' => 'media/library/report.pdf',
        'stored_name' => 'report.pdf',
        'original_name' => 'report.pdf',
    ]);

    Storage::disk('local')->put($media->path, 'test-report');

    $this->actingAs($admin)
        ->get(route('media.download', $media))
        ->assertOk();

    $this->actingAs($admin)
        ->delete(route('media.destroy', $media))
        ->assertRedirect(route('media.index'));

    Storage::disk('local')->assertMissing($media->path);
    expect(Media::query()->whereKey($media->id)->exists())->toBeFalse();
});

test('member cannot access media routes', function () {
    $this->seed(RolePermissionSeeder::class);

    $member = User::factory()->create();
    $member->assignRole('Staff');

    $this->actingAs($member)
        ->get(route('media.index'))
        ->assertForbidden();
});
