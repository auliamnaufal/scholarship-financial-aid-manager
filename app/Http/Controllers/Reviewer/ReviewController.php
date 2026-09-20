<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Application;
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
            ->with('status', 'Review submitted.');
    }
}
