<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Application $application): bool
    {
        // A coordinator decides on their own scholarships, so they may not also
        // score them; they review everyone else's.
        return $user->isReviewer()
            && $application->program->coordinator_id !== $user->id
            && $application->status === ApplicationStatus::UnderReview
            && ! $application->reviews()->where('reviewer_id', $user->id)->exists();
    }

    /**
     * A reviewer may revise their own assessment while the application is
     * still under review. Once the coordinator approves or rejects it the
     * score is locked, because the decision was made on those numbers.
     */
    public function update(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id
            && $review->application->status === ApplicationStatus::UnderReview;
    }
}
