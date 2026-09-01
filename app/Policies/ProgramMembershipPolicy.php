<?php

namespace App\Policies;

use App\Models\ProgramMembership;
use App\Models\User;
use App\Policies\Concerns\InteractsWithProgramScope;

class ProgramMembershipPolicy
{
    use InteractsWithProgramScope;

    public function update(User $user, ProgramMembership $membership): bool
    {
        return $membership->status === 'active'
            && $user->can('program.update')
            && $this->hasActiveProgramStaffScope($user, $membership->program);
    }
}
