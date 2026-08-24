<?php

use App\Models\Media;
use App\Models\User;
use App\Support\MediaUploader;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('non-image uploads are stored without thumbnails', function () {
    Storage::fake('local');

    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $media = MediaUploader::store(
        file: UploadedFile::fake()->createWithContent('report.pdf', '%PDF-1.4 test'),
        user: $admin,
    );

    expect($media->thumbnail_path)->toBeNull()
        ->and(MediaUploader::generateThumbnail($media))->toBeNull();
});

test('image uploads generate a thumbnail variant when a driver is available', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('The PHP GD extension is required for thumbnail generation.');
    }

    Storage::fake('local');

    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $media = MediaUploader::store(
        file: UploadedFile::fake()->image('photo.png', 1600, 1200),
        user: $admin,
    );

    $withThumbnail = MediaUploader::generateThumbnail($media);

    expect($withThumbnail)->not->toBeNull()
        ->and($withThumbnail->thumbnail_path)->not->toBeNull()
        ->and(Storage::disk('local')->exists($withThumbnail->path))->toBeTrue()
        ->and(Storage::disk('local')->exists((string) $withThumbnail->thumbnail_path))->toBeTrue();

    [$width] = getimagesizefromstring(
        Storage::disk('local')->get((string) $withThumbnail->thumbnail_path),
    );

    expect($width)->toBeLessThanOrEqual(400);
});

test('deleting media removes the original and its thumbnail', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('The PHP GD extension is required for thumbnail generation.');
    }

    Storage::fake('local');

    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $media = MediaUploader::generateThumbnail(
        MediaUploader::store(
            file: UploadedFile::fake()->image('photo.jpg', 900, 900),
            user: $admin,
        ),
    );

    $originalPath = $media->path;
    $thumbnailPath = (string) $media->thumbnail_path;

    MediaUploader::deleteFiles($media);

    expect(Storage::disk('local')->exists($originalPath))->toBeFalse()
        ->and(Storage::disk('local')->exists($thumbnailPath))->toBeFalse();
});

test('the admin media page reports thumbnail availability', function () {
    Storage::fake('local');

    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    Media::factory()->create(['thumbnail_path' => null]);

    $props = $this->actingAs($admin)
        ->get(route('media.index'))
        ->assertOk()
        ->inertiaProps();

    expect($props['media']['data'][0]['hasThumbnail'])->toBeFalse();
});
