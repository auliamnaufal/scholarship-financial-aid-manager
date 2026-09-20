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

        $application->load(['student.studentProfile', 'program', 'reviews.reviewer', 'disbursements']);

        return view('coordinator.applications.show', compact('application'));
    }

    public function approve(Application $application): RedirectResponse
    {
        $this->authorize('decide', $application);

        $application->update(['status' => ApplicationStatus::Approved]);

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
