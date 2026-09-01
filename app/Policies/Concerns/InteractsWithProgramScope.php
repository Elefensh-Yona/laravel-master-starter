<?php

namespace App\Policies\Concerns;

use App\Models\Program;
use App\Models\ProgramMembership;
use App\Models\User;

trait InteractsWithProgramScope
{
    private const PROGRAM_STAFF_CAPABILITY = 'program_staff';

    private function hasActiveProgramScope(User $user, Program $program): bool
    {
        return ProgramMembership::query()
            ->where('program_id', $program->getKey())
            ->where('user_id', $user->getKey())
            ->where('status', 'active')
            ->exists();
    }

    private function hasActiveProgramStaffScope(User $user, Program $program): bool
    {
        return ProgramMembership::query()
            ->where('program_id', $program->getKey())
            ->where('user_id', $user->getKey())
            ->where('capability', self::PROGRAM_STAFF_CAPABILITY)
            ->where('status', 'active')
            ->exists();
    }
}
