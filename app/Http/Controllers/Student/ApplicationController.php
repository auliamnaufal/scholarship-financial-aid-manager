<?php

namespace App\Http\Controllers\Student;

use App\Enums\ApplicationStatus;
use App\Enums\RequirementKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Program;
use App\Models\ProgramRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function create(Program $program): View
    {
        $this->authorize('create', Application::class);

        $program->load('requirements.requirementType');

        return view('student.applications.create', [
            'program' => $program,
            'profile' => Auth::user()->studentProfile,
        ]);
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $application = DB::transaction(function () use ($request) {
            $application = Auth::user()->applications()->create([
                'program_id' => $request->validated('program_id'),
                'semester' => $request->validated('semester'),
                'submission_date' => Date::today(),
                'status' => ApplicationStatus::Submitted,
            ]);

            foreach ($request->program()->requirements as $requirement) {
                $this->storeAnswer($application, $requirement, $request->file("answers.{$requirement->requirement_type_id}")
                    ?? $request->input("answers.{$requirement->requirement_type_id}"));
            }

            return $application;
        });

        return redirect()
            ->route('student.applications.show', $application)
            ->with('status', 'Application submitted successfully.');
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'program.requirements.requirementType',
            'documents.requirementType',
            'reviews',
            'disbursements',
        ]);

        return view('student.applications.show', compact('application'));
    }

    /**
     * Withdraws the application. Allowed while it is still waiting or being
     * looked at; once the coordinator has decided, it is history. Reviews
     * already written stay on the record.
     */
    public function cancel(Application $application): RedirectResponse
    {
        $this->authorize('cancel', $application);

        $application->update([
            'status' => ApplicationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('student.dashboard')
            ->with('status', 'Application withdrawn.');
    }

    /**
     * Saves one answer. Files go to the private disk under the application, so
     * a transcript or a recommendation letter never sits on a public URL.
     */
    private function storeAnswer(Application $application, ProgramRequirement $requirement, mixed $answer): void
    {
        if (blank($answer)) {
            return;
        }

        $type = $requirement->requirementType;

        if ($type->kind === RequirementKind::Text) {
            $application->documents()->create([
                'requirement_type_id' => $type->id,
                'body' => $answer,
            ]);

            return;
        }

        if (! $answer instanceof UploadedFile) {
            return;
        }

        $application->documents()->create([
            'requirement_type_id' => $type->id,
            'file_path' => $answer->store("applications/{$application->id}", 'local'),
            'original_name' => $answer->getClientOriginalName(),
            'mime_type' => $answer->getClientMimeType(),
            'size_bytes' => $answer->getSize(),
        ]);
    }
}
