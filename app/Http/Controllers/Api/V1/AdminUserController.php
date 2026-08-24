<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AdminUserResource;
use App\Models\User;
use App\Support\ApiPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->with('roles')
            ->when($search !== '', fn ($query) => $query->searchLike(['name', 'email'], $search))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return ApiPagination::response(
            request: $request,
            paginator: $users,
            resourceCollection: AdminUserResource::collection($users->getCollection()),
            meta: [
                'filters' => [
                    'search' => $search,
                ],
            ],
        );
    }
}
