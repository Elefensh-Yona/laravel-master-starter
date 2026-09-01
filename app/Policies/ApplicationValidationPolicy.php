<?php

namespace App\Policies;

use App\Models\ApplicationValidation;
use App\Models\User;
use App\Policies\Concerns\InteractsWithProgramScope;

class ApplicationValidationPolicy
{
    use InteractsWithProgramScope;

    public function view(User $user, ApplicationValidation $validation): bool
    {
        return $user->can('eligibility.validate')
            && $this->hasActiveProgramStaffScope($user, $validation->program);
    }

    public function create(User $user): bool
    {
        return $user->can('eligibility.validate');
    }

    public function update(User $user, ApplicationValidation $validation): bool
    {
        return $user->can('eligibility.validate')
            && $this->hasActiveProgramStaffScope($user, $validation->program);
    }
}
