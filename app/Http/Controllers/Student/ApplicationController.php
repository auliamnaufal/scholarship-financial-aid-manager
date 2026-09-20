<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function create(Program $program): View
    {
        $this->authorize('create', Application::class);

        return view('student.applications.create', compact('program'));
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $application = Auth::user()->applications()->create([
            'program_id' => $request->validated('program_id'),
            'semester' => $request->validated('semester'),
            'submission_date' => Date::today(),
            'status' => 'submitted',
        ]);

        return redirect()
            ->route('student.applications.show', $application)
            ->with('status', 'Application submitted successfully.');
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load(['program', 'reviews', 'disbursements']);

        return view('student.applications.show', compact('application'));
    }
}
