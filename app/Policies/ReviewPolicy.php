<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Application $application): bool
    {
        return $user->hasRole('reviewer')
            && $application->status === ApplicationStatus::UnderReview
            && ! $application->reviews()->where('reviewer_id', $user->id)->exists();
    }
}
