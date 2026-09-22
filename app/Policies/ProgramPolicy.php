<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('coordinator');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('coordinator');
    }

    public function update(User $user, Program $program): bool
    {
        return $program->coordinator_id === $user->id;
    }

    /**
     * Archiving, not erasing: the applications, reviews and payment records
     * hanging off this programme all cascade, so a hard delete would take them
     * with it.
     */
    public function delete(User $user, Program $program): bool
    {
        return $program->coordinator_id === $user->id;
    }

    public function restore(User $user, Program $program): bool
    {
        return $program->coordinator_id === $user->id;
    }

    public function viewApplications(User $user, Program $program): bool
    {
        return $program->coordinator_id === $user->id;
    }
}
