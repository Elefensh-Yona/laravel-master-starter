<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;
use App\Policies\Concerns\InteractsWithProgramScope;

class ProgramPolicy
{
    use InteractsWithProgramScope;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Program $program): bool
    {
        return $program->status === 'published'
            || ($user->can('program.view') && $this->hasActiveProgramScope($user, $program));
    }

    public function create(User $user): bool
    {
        return $user->can('program.create');
    }

    public function update(User $user, Program $program): bool
    {
        return $user->can('program.update')
            && $this->hasActiveProgramStaffScope($user, $program)
            && $program->status !== 'archived';
    }

    public function publish(User $user, Program $program): bool
    {
        return $user->can('program.publish')
            && $this->hasActiveProgramStaffScope($user, $program)
            && $program->status === 'draft'
            && $program->opens_at->lessThan($program->closes_at);
    }
}
