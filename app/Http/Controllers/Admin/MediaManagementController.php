<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Models\Media;
use App\Support\ActivityLogger;
use App\Support\MediaUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Media::class);

        $search = $request->string('search')->trim()->toString();

        return Inertia::render('admin/Media/Index', [
            'media' => Media::query()
                ->with('uploadedBy:id,name,email')
                ->when($search !== '', fn ($query) => $query->searchLike(['original_name', 'collection', 'mime_type'], $search))
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Media $media): array => $this->mediaSummary($media)),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $this->authorize('create', Media::class);

        $media = MediaUploader::store(
            file: $request->file('file'),
            user: $request->user(),
            collection: $request->string('collection')->trim()->toString() ?: 'library',
        );

        MediaUploader::generateThumbnail($media);

        ActivityLogger::record(
            actor: $request->user(),
            event: 'media.created',
            description: "Uploaded file {$media->original_name}.",
            subject: $media,
            properties: [
                'collection' => $media->collection,
                'size' => $media->size,
            ],
            request: $request,
        );

        return to_route('media.index')->with('success', 'File uploaded successfully.');
    }

    public function download(Media $media): StreamedResponse
    {
        $this->authorize('view', $media);

        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        return Storage::disk($media->disk)->download($media->path, $media->original_name);
    }

    public function destroy(Request $request, Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        MediaUploader::deleteFiles($media);

        ActivityLogger::record(
            actor: $request->user(),
            event: 'media.deleted',
            description: "Deleted file {$media->original_name}.",
            subject: $media,
            properties: [
                'collection' => $media->collection,
            ],
            request: $request,
        );

        $media->delete();

        return to_route('media.index')->with('success', 'File deleted successfully.');
    }

    /**
     * @return array{id: int, collection: string, originalName: string, extension: string|null, mimeType: string|null, size: int, uploadedBy: string|null, createdAt: string|null, downloadUrl: string, hasThumbnail: bool}
     */
    private function mediaSummary(Media $media): array
    {
        return [
            'id' => $media->id,
            'collection' => $media->collection,
            'originalName' => $media->original_name,
            'extension' => $media->extension,
            'mimeType' => $media->mime_type,
            'size' => $media->size,
            'uploadedBy' => $media->uploadedBy?->name,
            'createdAt' => $media->created_at?->toDateTimeString(),
            'downloadUrl' => route('media.download', $media),
            'hasThumbnail' => $media->thumbnail_path !== null
                && Storage::disk($media->disk)->exists($media->thumbnail_path),
        ];
    }
}
