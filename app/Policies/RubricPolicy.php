<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\Rubric;
use App\Models\User;
use App\Policies\Concerns\InteractsWithProgramScope;

class RubricPolicy
{
    use InteractsWithProgramScope;

    public function view(User $user, Rubric $rubric): bool
    {
        return $user->can('rubric.view')
            && $this->hasActiveProgramScope($user, $rubric->program);
    }

    public function create(User $user, Program $program): bool
    {
        return $user->can('rubric.create')
            && $this->hasActiveProgramStaffScope($user, $program)
            && $program->status !== 'archived';
    }

    public function update(User $user, Rubric $rubric): bool
    {
        return $rubric->status === 'draft'
            && $user->can('rubric.update')
            && $this->hasActiveProgramStaffScope($user, $rubric->program);
    }
}
