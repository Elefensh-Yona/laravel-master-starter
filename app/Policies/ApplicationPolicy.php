<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use App\Policies\Concerns\InteractsWithProgramScope;

class ApplicationPolicy
{
    use InteractsWithProgramScope;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Application $application): bool
    {
        if ($application->primary_owner_id === $user->getKey()) {
            return true;
        }

        return $user->can('application.view')
            && $this->hasActiveProgramScope($user, $application->program);
    }

    public function create(User $user): bool
    {
        return $user->can('application.create');
    }

    public function update(User $user, Application $application): bool
    {
        return $user->can('application.update')
            && $application->primary_owner_id === $user->getKey();
    }

    public function submit(User $user, Application $application): bool
    {
        return $user->can('application.submit')
            && $application->primary_owner_id === $user->getKey()
            && $application->currentVersion?->status === 'draft';
    }
}
