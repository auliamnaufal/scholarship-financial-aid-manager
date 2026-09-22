<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $programIds = Auth::user()->programsManaged()->pluck('id');

        $applications = Application::query()
            ->whereIn('program_id', $programIds)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->get();

        return view('coordinator.applications.index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::cases(),
            'selectedStatus' => $request->string('status')->toString(),
        ]);
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'student.studentProfile',
            'program.requirements.requirementType',
            'documents.requirementType',
            'reviews.reviewer',
            'disbursements',
        ]);

        return view('coordinator.applications.show', compact('application'));
    }

    public function approve(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('decide', $application);

        $validated = $request->validate([
            // What the student is promised. Disbursements are then checked
            // against it, so the total paid can never exceed the award.
            'awarded_amount' => [
                'required', 'numeric', 'min:0.01',
                'max:'.$application->program->remainingBudget(),
            ],
        ], [
            'awarded_amount.max' => 'The program has only :max left in its budget.',
        ]);

        $application->update([
            'status' => ApplicationStatus::Approved,
            'awarded_amount' => $validated['awarded_amount'],
        ]);

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', 'Application approved.');
    }

    public function reject(Application $application): RedirectResponse
    {
        $this->authorize('decide', $application);

        $application->update(['status' => ApplicationStatus::Rejected]);

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', 'Application rejected.');
    }
}
