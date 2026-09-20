<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;

class DisbursementPolicy
{
    public function create(User $user, Application $application): bool
    {
        return $user->hasRole('coordinator')
            && $application->program->coordinator_id === $user->id
            && $application->status === ApplicationStatus::Approved;
    }
}
