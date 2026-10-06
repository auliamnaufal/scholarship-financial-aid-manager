<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Disbursement;
use App\Models\User;

class DisbursementPolicy
{
    public function create(User $user, Application $application): bool
    {
        return $this->manages($user, $application);
    }

    /** Correcting a payment is open to the same people, on the same applications, as recording one. */
    public function update(User $user, Disbursement $disbursement): bool
    {
        return $this->manages($user, $disbursement->application);
    }

    public function delete(User $user, Disbursement $disbursement): bool
    {
        return $this->manages($user, $disbursement->application);
    }

    private function manages(User $user, Application $application): bool
    {
        return $user->hasRole('coordinator')
            && $application->program->coordinator_id === $user->id
            && $application->status === ApplicationStatus::Approved;
    }
}
