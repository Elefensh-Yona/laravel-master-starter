<?php

namespace App\Policies;

use App\Models\ApplicationVersion;
use App\Models\User;

class ApplicationVersionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ApplicationVersion $version): bool
    {
        return $version->application->primary_owner_id === $user->getKey()
            || $user->can('application.view');
    }

    public function update(User $user, ApplicationVersion $version): bool
    {
        return $user->can('application.update')
            && $version->application->primary_owner_id === $user->getKey();
    }
}
