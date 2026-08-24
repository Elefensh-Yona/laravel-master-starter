<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'email_verified_at' => $this->resource->email_verified_at?->toISOString(),
            'roles' => $this->resource->getRoleNames()->values()->all(),
            'permissions' => $this->resource->getAllPermissions()->pluck('name')->values()->all(),
            'locale' => $this->resource->effectiveLocale(),
        ];
    }
}
