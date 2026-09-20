<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('coordinator');
    }

    public function update(User $user, Program $program): bool
    {
        return $program->coordinator_id === $user->id;
    }

    public function viewApplications(User $user, Program $program): bool
    {
        return $program->coordinator_id === $user->id;
    }
}
