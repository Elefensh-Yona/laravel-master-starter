<?php

namespace App\Policies;

use App\Models\Screening;
use App\Models\User;
use App\Policies\Concerns\InteractsWithProgramScope;

class ScreeningPolicy
{
    use InteractsWithProgramScope;

    public function view(User $user, Screening $screening): bool
    {
        return $user->can('eligibility.screen')
            && $this->hasActiveProgramStaffScope($user, $screening->program);
    }

    public function create(User $user): bool
    {
        return $user->can('eligibility.screen');
    }

    public function update(User $user, Screening $screening): bool
    {
        return $user->can('eligibility.screen')
            && $this->hasActiveProgramStaffScope($user, $screening->program);
    }
}
