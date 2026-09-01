<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\ApplicationValidation;
use App\Models\ApplicationVersion;
use App\Models\Media;
use App\Models\Program;
use App\Models\ProgramEligibilityRule;
use App\Models\ProgramMembership;
use App\Models\Rubric;
use App\Models\Screening;
use App\Models\Setting;
use App\Models\User;
use App\Policies\ApplicationMemberPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\ApplicationValidationPolicy;
use App\Policies\ApplicationVersionPolicy;
use App\Policies\MediaPolicy;
use App\Policies\ProgramEligibilityRulePolicy;
use App\Policies\ProgramMembershipPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\RolePolicy;
use App\Policies\RubricPolicy;
use App\Policies\ScreeningPolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use App\Support\ActivityLogger;
use App\Support\SystemRole;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
        $this->configureQueryBuilderMacros();
    }

    /**
     * Register reusable query builder macros.
     */
    protected function configureQueryBuilderMacros(): void
    {
        EloquentBuilder::macro('searchLike', function (array $columns, string $term): EloquentBuilder {
            $pattern = '%'.str($term)->trim().'%';

            return $this->where(function (EloquentBuilder $query) use ($columns, $pattern): void {
                foreach ($columns as $column) {
                    $query->orWhereRaw("lower({$column}) like lower(?)", [$pattern]);
                }
            });
        });

        QueryBuilder::macro('searchLike', function (array $columns, string $term): QueryBuilder {
            $pattern = '%'.str($term)->trim().'%';

            return $this->where(function (QueryBuilder $query) use ($columns, $pattern): void {
                foreach ($columns as $column) {
                    $query->orWhereRaw("lower({$column}) like lower(?)", [$pattern]);
                }
            });
        });
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
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(ProgramMembership::class, ProgramMembershipPolicy::class);
        Gate::policy(ProgramEligibilityRule::class, ProgramEligibilityRulePolicy::class);
        Gate::policy(Rubric::class, RubricPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(ApplicationMember::class, ApplicationMemberPolicy::class);
        Gate::policy(ApplicationValidation::class, ApplicationValidationPolicy::class);
        Gate::policy(ApplicationVersion::class, ApplicationVersionPolicy::class);
        Gate::policy(Screening::class, ScreeningPolicy::class);

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
