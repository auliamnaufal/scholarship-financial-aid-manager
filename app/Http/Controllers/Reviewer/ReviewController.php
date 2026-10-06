<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Application;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Application $application): RedirectResponse
    {
        $application->reviews()->create([
            'reviewer_id' => Auth::id(),
            'score' => $request->validated('score'),
            'comments' => $request->validated('comments'),
        ]);

        return redirect()
            ->route('reviewer.dashboard')
            ->with('status', __('Review submitted.'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $application = $review->application;
        $review->delete();

        return redirect()
            ->route('reviewer.applications.show', $application)
            ->with('status', __('Review deleted. You can submit a new one.'));
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($request->validated());

        return redirect()
            ->route('reviewer.applications.show', $review->application)
            ->with('status', __('Review updated.'));
    }
}
