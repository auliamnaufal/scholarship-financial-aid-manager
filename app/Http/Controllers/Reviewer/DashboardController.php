<?php

namespace App\Http\Controllers\Reviewer;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $claimable = Application::query()
            ->where('status', ApplicationStatus::Submitted)
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->get();

        $awaitingMyReview = Application::query()
            ->where('status', ApplicationStatus::UnderReview)
            ->whereDoesntHave('reviews', function ($query) {
                $query->where('reviewer_id', Auth::id());
            })
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->get();

        $allSubmissions = Application::query()
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->get();

        return view('reviewer.dashboard', compact('claimable', 'awaitingMyReview', 'allSubmissions'));
    }
}
