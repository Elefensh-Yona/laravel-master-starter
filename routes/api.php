<?php

use App\Http\Controllers\Api\V1\ActivityLogApiController;
use App\Http\Controllers\Api\V1\AdminSummaryController;
use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CurrentUserController;
use App\Http\Controllers\Api\V1\MediaApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('login', [AuthTokenController::class, 'store'])->middleware('guest');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', CurrentUserController::class);
            Route::post('logout', [AuthTokenController::class, 'destroy']);
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('notifications', [NotificationApiController::class, 'index'])
            ->middleware('permission:notifications.view');
        Route::get('notifications/{notification}', [NotificationApiController::class, 'show'])
            ->middleware('permission:notifications.view');
        Route::put('notifications/{notification}/read', [NotificationApiController::class, 'update'])
            ->middleware('permission:notifications.view');
        Route::post('notifications/read-all', [NotificationApiController::class, 'destroy'])
            ->middleware('permission:notifications.view');
        Route::get('activity-logs', [ActivityLogApiController::class, 'index'])
            ->middleware('permission:activity-logs.view');
        Route::get('activity-logs/{activityLog}', [ActivityLogApiController::class, 'show'])
            ->middleware('permission:activity-logs.view');
        Route::get('admin/summary', AdminSummaryController::class)
            ->middleware('permission:users.view');
        Route::get('admin/users', AdminUserController::class)
            ->middleware('permission:users.view');
        Route::get('media', [MediaApiController::class, 'index'])
            ->middleware('permission:media.view');
    });
});
