<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Application $application): bool
    {
        return $application->primary_owner_id === $user->getKey()
            || $application->status === 'submitted'
            || $user->can('application.view');
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
