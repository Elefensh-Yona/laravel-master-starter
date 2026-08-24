<?php

namespace App\Support;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class MediaUploader
{
    /**
     * Maximum thumbnail edge in pixels (longest side).
     */
    private const THUMBNAIL_SIZE = 400;

    public static function store(UploadedFile $file, User $user, string $collection = 'library', string $disk = 'local'): Media
    {
        $normalizedCollection = str($collection)->trim()->lower()->slug()->toString() ?: 'library';
        $directory = 'media/'.$normalizedCollection;
        $storedPath = $file->store($directory, $disk);

        return Media::query()->create([
            'uploaded_by' => $user->id,
            'collection' => $normalizedCollection,
            'disk' => $disk,
            'directory' => $directory,
            'path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => basename($storedPath),
            'extension' => $file->getClientOriginalExtension() ?: null,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'metadata' => [
                'generated_name' => $file->hashName(),
            ],
        ]);
    }

    /**
     * Generate and persist a downscaled thumbnail for an image media record.
     *
     * Silently skips non-images and environments without a usable image
     * driver; the media record simply remains without a thumbnail.
     */
    public static function generateThumbnail(Media $media): ?Media
    {
        if ($media->thumbnail_path !== null) {
            return $media;
        }

        $mime = (string) $media->mime_type;

        if (! str_starts_with($mime, 'image/') || str_contains($mime, 'svg')) {
            return null;
        }

        if (! extension_loaded('gd')) {
            return null;
        }

        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            return null;
        }

        try {
            $image = (new ImageManager(new GdDriver))->read($disk->readStream($media->path));
            $image->scaleDown(width: self::THUMBNAIL_SIZE, height: self::THUMBNAIL_SIZE);

            $thumbnailPath = $media->directory.'/'.$media->stored_name.'-thumb.jpg';

            $disk->put($thumbnailPath, $image->toJpeg(quality: 80));

            $media->forceFill(['thumbnail_path' => $thumbnailPath])->save();

            return $media->refresh();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Delete stored files for a media record, thumbnails included.
     */
    public static function deleteFiles(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        $disk->delete($media->path);

        if ($media->thumbnail_path !== null) {
            $disk->delete($media->thumbnail_path);
        }
    }
}
