<?php

namespace App\Http\Controllers\Reviewer;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function claim(Application $application): RedirectResponse
    {
        $this->authorize('claim', $application);

        $application->update(['status' => ApplicationStatus::UnderReview]);

        return redirect()
            ->route('reviewer.applications.show', $application)
            ->with('status', 'Application claimed. You can now submit your review.');
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'student.studentProfile',
            'program.requirements.requirementType',
            'documents.requirementType',
            'reviews.reviewer',
        ]);

        return view('reviewer.applications.show', compact('application'));
    }
}
