<?php

namespace App\Policies;

use App\Models\ProgramEligibilityRule;
use App\Models\User;
use App\Policies\Concerns\InteractsWithProgramScope;

class ProgramEligibilityRulePolicy
{
    use InteractsWithProgramScope;

    public function view(User $user, ProgramEligibilityRule $rule): bool
    {
        return ($rule->program->status === 'published' && $rule->is_enabled)
            || ($user->can('eligibility.view') && $this->hasActiveProgramScope($user, $rule->program));
    }

    public function validate(User $user, ProgramEligibilityRule $rule): bool
    {
        return $rule->is_enabled
            && $user->can('eligibility.validate')
            && $this->hasActiveProgramStaffScope($user, $rule->program);
    }

    public function screen(User $user, ProgramEligibilityRule $rule): bool
    {
        return $user->can('eligibility.screen')
            && $this->hasActiveProgramStaffScope($user, $rule->program);
    }
}
