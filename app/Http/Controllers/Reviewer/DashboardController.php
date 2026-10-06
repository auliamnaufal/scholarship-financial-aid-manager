<?php

namespace App\Http\Controllers\Reviewer;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // A coordinator cannot review their own scholarships, so those do not
        // show up as work to pick up.
        $notMine = fn ($query) => $query->where('coordinator_id', '!=', Auth::id());

        $claimable = Application::query()
            ->where('status', ApplicationStatus::Submitted)
            ->whereHas('program', $notMine)
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->get();

        $awaitingMyReview = Application::query()
            ->where('status', ApplicationStatus::UnderReview)
            ->whereHas('program', $notMine)
            ->whereDoesntHave('reviews', function ($query) {
                $query->where('reviewer_id', Auth::id());
            })
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->get();

        $allSubmissions = Application::query()
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->latest('id')
            ->paginate(10);

        $reviewedByMe = Review::where('reviewer_id', Auth::id())->count();

        return view('reviewer.dashboard', compact('claimable', 'awaitingMyReview', 'allSubmissions', 'reviewedByMe'));
    }
}
