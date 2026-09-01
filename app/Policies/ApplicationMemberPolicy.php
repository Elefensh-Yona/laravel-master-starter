<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\User;

class ApplicationMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('application.view');
    }

    public function create(User $user, Application $application): bool
    {
        return $user->can('application.update')
            && $application->primary_owner_id === $user->getKey();
    }

    public function view(User $user, ApplicationMember $member): bool
    {
        return $member->application->primary_owner_id === $user->getKey()
            || $member->user_id === $user->getKey()
            || $user->can('application.view');
    }

    public function update(User $user, ApplicationMember $member): bool
    {
        return $member->status === 'active'
            && $user->can('application.update')
            && $member->application->primary_owner_id === $user->getKey();
    }

    public function delete(User $user, ApplicationMember $member): bool
    {
        return $this->update($user, $member);
    }
}
