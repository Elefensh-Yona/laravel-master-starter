<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\MediaManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\SettingsManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EligibilityValidationController;
use App\Http\Controllers\ExportCenterController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ScreeningController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user === null) {
        return redirect()->route('login');
    }

    return $user->can('dashboard.view')
        ? redirect()->route('dashboard')
        : redirect()->route('profile.edit');
})->name('home');

Route::middleware(['auth', 'verified', 'permission:dashboard.view'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('programs/create', [ProgramController::class, 'create'])
        ->middleware('permission:program.create')
        ->name('programs.create');
    Route::post('programs', [ProgramController::class, 'store'])
        ->middleware('permission:program.create')
        ->name('programs.store');
    Route::get('programs/{program}/edit', [ProgramController::class, 'edit'])
        ->middleware('permission:program.update')
        ->name('programs.edit');
    Route::put('programs/{program}', [ProgramController::class, 'update'])
        ->middleware('permission:program.update')
        ->name('programs.update');
    Route::post('programs/{program}/publish', [ProgramController::class, 'publish'])
        ->middleware('permission:program.publish')
        ->name('programs.publish');
    Route::get('programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/create', [ApplicationController::class, 'create'])
        ->middleware('permission:application.create')
        ->name('applications.create');
    Route::post('applications', [ApplicationController::class, 'store'])
        ->middleware('permission:application.create')
        ->name('applications.store');
    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::get('applications/{application}/edit', [ApplicationController::class, 'edit'])
        ->middleware('permission:application.update')
        ->name('applications.edit');
    Route::put('applications/{application}', [ApplicationController::class, 'update'])
        ->middleware('permission:application.update')
        ->name('applications.update');
    Route::get('applications/{application}/members', [ApplicationMemberController::class, 'index'])
        ->middleware('permission:application.view')
        ->name('applications.members.index');
    Route::post('applications/{application}/members', [ApplicationMemberController::class, 'store'])
        ->middleware('permission:application.update')
        ->name('applications.members.store');
    Route::put('applications/{application}/members/{member}', [ApplicationMemberController::class, 'update'])
        ->middleware('permission:application.update')
        ->name('applications.members.update');
    Route::delete('applications/{application}/members/{member}', [ApplicationMemberController::class, 'destroy'])
        ->middleware('permission:application.update')
        ->name('applications.members.destroy');
    Route::post('applications/{application}/submit', [ApplicationController::class, 'submit'])
        ->middleware('permission:application.submit')
        ->name('applications.submit');
    Route::post('applications/{application}/revise', [ApplicationController::class, 'revise'])
        ->middleware('permission:application.update')
        ->name('applications.revise');

    Route::get('applications/{application}/eligibility-validations', [EligibilityValidationController::class, 'index'])
        ->middleware('permission:application.view')
        ->name('eligibility-validations.index');
    Route::get('applications/{application}/eligibility-validations/{validation}', [EligibilityValidationController::class, 'show'])
        ->middleware('permission:application.view')
        ->name('eligibility-validations.show');
    Route::post('applications/{application}/eligibility-validations', [EligibilityValidationController::class, 'store'])
        ->middleware('permission:eligibility.validate')
        ->name('eligibility-validations.store');

    Route::get('applications/{application}/screenings', [ScreeningController::class, 'index'])
        ->middleware('permission:application.view')
        ->name('screenings.index');
    Route::get('applications/{application}/screenings/{screening}', [ScreeningController::class, 'show'])
        ->middleware('permission:application.view')
        ->name('screenings.show');
    Route::post('applications/{application}/screenings', [ScreeningController::class, 'store'])
        ->middleware('permission:eligibility.screen')
        ->name('screenings.store');
    Route::put('applications/{application}/screenings/{screening}', [ScreeningController::class, 'update'])
        ->middleware('permission:eligibility.screen')
        ->name('screenings.update');

    Route::get('search', GlobalSearchController::class)
        ->middleware('permission:search.view')
        ->name('search.index');

    Route::get('exports', [ExportCenterController::class, 'index'])
        ->middleware('permission:exports.view')
        ->name('exports.index');

    Route::get('exports/users.csv', [ExportCenterController::class, 'usersCsv'])
        ->middleware(['permission:exports.view', 'permission:users.view'])
        ->name('exports.users.csv');

    Route::get('exports/users.xlsx', [ExportCenterController::class, 'usersXlsx'])
        ->middleware(['permission:exports.view', 'permission:users.view'])
        ->name('exports.users.xlsx');

    Route::get('exports/users.xml', [ExportCenterController::class, 'usersXml'])
        ->middleware(['permission:exports.view', 'permission:users.view'])
        ->name('exports.users.xml');

    Route::get('exports/summary/print', [ExportCenterController::class, 'printSummary'])
        ->middleware('permission:exports.view')
        ->name('exports.summary.print');

    Route::get('exports/summary.pdf', [ExportCenterController::class, 'summaryPdf'])
        ->middleware('permission:exports.view')
        ->name('exports.summary.pdf');

    Route::get('admin/users', [UserManagementController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('admin/settings', [SettingsManagementController::class, 'edit'])
        ->middleware('permission:settings.view')
        ->name('admin-settings.edit');

    Route::put('admin/settings', [SettingsManagementController::class, 'update'])
        ->middleware('permission:settings.update')
        ->name('admin-settings.update');

    Route::get('admin/media', [MediaManagementController::class, 'index'])
        ->middleware('permission:media.view')
        ->name('media.index');

    Route::post('admin/media', [MediaManagementController::class, 'store'])
        ->middleware('permission:media.create')
        ->name('media.store');

    Route::get('admin/media/{media}/download', [MediaManagementController::class, 'download'])
        ->middleware('permission:media.view')
        ->name('media.download');

    Route::delete('admin/media/{media}', [MediaManagementController::class, 'destroy'])
        ->middleware('permission:media.delete')
        ->name('media.destroy');

    Route::get('admin/users/create', [UserManagementController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');

    Route::post('admin/users', [UserManagementController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::get('admin/users/{user}/edit', [UserManagementController::class, 'edit'])
        ->middleware('permission:users.update')
        ->name('users.edit');

    Route::put('admin/users/{user}', [UserManagementController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');

    Route::put('admin/users/{user}/roles', [UserManagementController::class, 'updateRoles'])
        ->middleware('permission:users.update')
        ->name('users.roles.update');

    Route::delete('admin/users/{user}', [UserManagementController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    Route::get('admin/roles', [RoleManagementController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('admin/roles/create', [RoleManagementController::class, 'create'])
        ->middleware('permission:roles.create')
        ->name('roles.create');

    Route::post('admin/roles', [RoleManagementController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('roles.store');

    Route::get('admin/roles/{role}/edit', [RoleManagementController::class, 'edit'])
        ->middleware('permission:roles.update')
        ->name('roles.edit');

    Route::put('admin/roles/{role}', [RoleManagementController::class, 'update'])
        ->middleware('permission:roles.update')
        ->name('roles.update');

    Route::put('admin/roles/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])
        ->middleware('permission:roles.update')
        ->name('roles.permissions.update');

    Route::delete('admin/roles/{role}', [RoleManagementController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('roles.destroy');

    Route::get('notifications', [NotificationController::class, 'index'])
        ->middleware('permission:notifications.view')
        ->name('notifications.index');

    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])
        ->middleware('permission:notifications.view')
        ->name('notifications.read');

    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])
        ->middleware('permission:notifications.view')
        ->name('notifications.read-all');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:activity-logs.view')
        ->name('activity-logs.index');

    Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])
        ->middleware('permission:activity-logs.view')
        ->name('activity-logs.show');
});

require __DIR__.'/settings.php';
