<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Program;
use App\Support\ApplyRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $programIds = Auth::user()->programsManaged()->pluck('id');

        $applications = Application::query()
            ->whereIn('program_id', $programIds)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->string('q')).'%';

                $query->where(fn ($q) => $q
                    ->whereHas('student', fn ($s) => $s->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('program', fn ($p) => $p->where('name', 'like', $term)));
            })
            ->with(['student', 'program'])
            ->latest('submission_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

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

    /**
     * Approves one application. What the student receives is not chosen here:
     * every recipient of a scholarship gets the same share of its budget, and
     * the scholarship can only have as many recipients as its quota.
     */
    public function approve(Application $application): RedirectResponse
    {
        $this->authorize('decide', $application);

        if ($conflict = ApplyRules::approvalConflict($application)) {
            return back()->withErrors(['approval' => $conflict]);
        }

        $approved = DB::transaction(function () use ($application) {
            // Locked, so two coordinators approving at once cannot overfill the quota.
            $program = Program::query()->lockForUpdate()->findOrFail($application->program_id);

            if ($program->slotsLeft() <= 0) {
                return false;
            }

            $application->update([
                'status' => ApplicationStatus::Approved,
                'awarded_amount' => (string) $program->awardPerRecipient(),
            ]);

            return true;
        });

        if (! $approved) {
            return back()->withErrors([
                'approval' => __('This scholarship already has all :quota recipients it was meant for.', ['quota' => $application->program->quota]),
            ]);
        }

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', __('Application approved.'));
    }

    public function reject(Application $application): RedirectResponse
    {
        $this->authorize('decide', $application);

        $application->update(['status' => ApplicationStatus::Rejected]);

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', __('Application rejected.'));
    }
}
