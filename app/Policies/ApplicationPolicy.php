<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        if ($application->student_id === $user->id) {
            return true;
        }

        if ($user->hasRole('coordinator') && $application->program->coordinator_id === $user->id) {
            return true;
        }

        if ($user->hasRole('reviewer')) {
            // Reviewers can see every submission across every scholarship;
            // only claiming (see ApplicationPolicy::claim) and submitting a
            // review are further restricted.
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('student');
    }

    public function update(User $user, Application $application): bool
    {
        return $application->student_id === $user->id
            && $application->status === ApplicationStatus::Submitted;
    }

    /**
     * A student may withdraw while their application is still waiting or being
     * looked at. Once the coordinator has decided, it is history — and the
     * decision may already have money attached to it.
     */
    public function cancel(User $user, Application $application): bool
    {
        return $application->student_id === $user->id
            && $application->status->isOpen();
    }

    public function claim(User $user, Application $application): bool
    {
        return $user->hasRole('reviewer')
            && $application->status === ApplicationStatus::Submitted;
    }

    public function decide(User $user, Application $application): bool
    {
        return $user->hasRole('coordinator')
            && $application->program->coordinator_id === $user->id
            && $application->status === ApplicationStatus::UnderReview
            && $application->reviews()->exists();
    }
}
