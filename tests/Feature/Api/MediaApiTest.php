<?php

use App\Models\Media;
use App\Models\User;
use App\Support\MediaUploader;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('media api returns a paginated media listing', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    Media::factory()->count(12)->create();

    $token = $admin->createToken('api')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/media')
        ->assertSuccessful()
        ->assertJsonPath('meta.pagination.total', 12)
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'collection', 'original_name', 'mime_type', 'size'],
            ],
            'links',
            'meta',
        ]);
});

test('media api filters by search and collection', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    Media::factory()->create(['original_name' => 'contract.pdf', 'collection' => 'library']);
    Media::factory()->create(['original_name' => 'photo.png', 'collection' => 'avatars']);

    $token = $admin->createToken('api')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/media?search=contract')
        ->assertSuccessful()
        ->assertJsonPath('meta.pagination.total', 1)
        ->assertJsonPath('data.0.original_name', 'contract.pdf');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/media?collection=avatars')
        ->assertSuccessful()
        ->assertJsonPath('meta.pagination.total', 1)
        ->assertJsonPath('data.0.collection', 'avatars');
});

test('media api supports real uploads through the uploader', function () {
    Storage::fake('local');

    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $admin->createToken('api')->plainTextToken;

    $media = MediaUploader::store(
        file: UploadedFile::fake()->createWithContent('report.pdf', '%PDF-1.4 test'),
        user: $admin,
    );

    expect(Storage::disk('local')->exists($media->path))->toBeTrue();

    $token = $admin->createToken('api2')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/media?search=report')
        ->assertSuccessful()
        ->assertJsonPath('meta.pagination.total', 1);
});

test('users without media.view permission cannot access the media api', function () {
    $this->seed(RolePermissionSeeder::class);

    $guest = User::factory()->create();
    $guest->assignRole('Guest');

    $token = $guest->createToken('api')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/media')
        ->assertForbidden();
});

test('unauthenticated requests cannot access the media api', function () {
    $this->getJson('/api/v1/media')->assertUnauthorized();
});
