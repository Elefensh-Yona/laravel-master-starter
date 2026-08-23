<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use App\Policies\MediaPolicy;
use App\Policies\RolePolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use App\Support\ActivityLogger;
use App\Support\SystemRole;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        Gate::before(fn (User $user, string $ability): ?bool => $user->hasRole(SystemRole::SUPER_ADMIN) ? true : null);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        Event::listen(function (Login $event): void {
            ActivityLogger::record(
                actor: $event->user,
                event: 'auth.login',
                description: 'Signed in successfully.',
                subject: $event->user,
            );
        });

        Event::listen(function (Logout $event): void {
            if ($event->user === null) {
                return;
            }

            ActivityLogger::record(
                actor: $event->user,
                event: 'auth.logout',
                description: 'Signed out successfully.',
                subject: $event->user,
            );
        });
    }
}
