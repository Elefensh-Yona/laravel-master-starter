<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Media */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection' => $this->collection,
            'original_name' => $this->original_name,
            'stored_name' => $this->stored_name,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'path' => $this->path,
            'disk' => $this->disk,
            'has_thumbnail' => $this->thumbnail_path !== null,
            'uploaded_by' => $this->uploaded_by,
            'attachable_type' => $this->attachable_type,
            'attachable_id' => $this->attachable_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
