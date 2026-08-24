<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MediaResource;
use App\Models\Media;
use App\Support\ApiPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaApiController extends Controller
{
    /**
     * List media files accessible to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $collection = $request->string('collection')->trim()->toString();

        $media = Media::query()
            ->with('uploadedBy:id,name,email')
            ->when($search !== '', fn ($query) => $query->searchLike(['original_name', 'collection', 'mime_type'], $search))
            ->when($collection !== '', function ($query) use ($collection): void {
                $query->where('collection', $collection);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return ApiPagination::response(
            request: $request,
            paginator: $media,
            resourceCollection: MediaResource::collection($media->getCollection()),
            meta: [
                'filters' => [
                    'search' => $search,
                    'collection' => $collection,
                ],
            ],
        );
    }
}
