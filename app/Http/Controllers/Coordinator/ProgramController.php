<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use App\Models\RequirementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Program::class);

        $programs = Auth::user()->programsManaged()
            ->withTrashed()
            ->withCount('applications')
            ->orderByRaw('deleted_at is not null')
            ->orderBy('application_deadline')
            ->get();

        return view('coordinator.programs.index', compact('programs'));
    }

    public function create(): View
    {
        $this->authorize('create', Program::class);

        return view('coordinator.programs.create', [
            'requirementTypes' => RequirementType::orderBy('name')->get(),
        ]);
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = DB::transaction(function () use ($request) {
            $program = Auth::user()->programsManaged()->create($request->programAttributes());

            $this->syncRequirements($program, $request->requirements());

            return $program;
        });

        return redirect()
            ->route('coordinator.programs.index')
            ->with('status', "Program \"{$program->name}\" created.");
    }

    public function edit(Program $program): View
    {
        $this->authorize('update', $program);

        $program->load('requirements');

        return view('coordinator.programs.edit', [
            'program' => $program,
            'requirementTypes' => RequirementType::orderBy('name')->get(),
        ]);
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        DB::transaction(function () use ($request, $program) {
            $program->update($request->programAttributes());

            $this->syncRequirements($program, $request->requirements());
        });

        return redirect()
            ->route('coordinator.programs.index')
            ->with('status', "Program \"{$program->name}\" updated.");
    }

    /**
     * Archives the scholarship. It leaves the public listing and takes no new
     * applications, but the ones already made keep working — every foreign key
     * here cascades, so erasing the row would erase their reviews and payment
     * records too.
     */
    public function destroy(Program $program): RedirectResponse
    {
        $this->authorize('delete', $program);

        $program->delete();

        return redirect()
            ->route('coordinator.programs.index')
            ->with('status', "Program \"{$program->name}\" archived.");
    }

    public function restore(int $program): RedirectResponse
    {
        $program = Program::withTrashed()->findOrFail($program);

        $this->authorize('restore', $program);

        $program->restore();

        return redirect()
            ->route('coordinator.programs.index')
            ->with('status', "Program \"{$program->name}\" restored.");
    }

    /**
     * Replaces the programme's checklist with what was submitted.
     *
     * Requirements already answered by an applicant are kept even if the
     * coordinator unticks them: deleting the row would cascade away the
     * documents students have already uploaded against it.
     *
     * @param  array<int, array{is_required: bool, instructions: ?string}>  $requirements
     */
    private function syncRequirements(Program $program, array $requirements): void
    {
        $answered = $program->requirements()
            ->whereHas('requirementType.applicationDocuments', fn ($query) => $query
                ->whereIn('application_id', $program->applications()->select('id')))
            ->pluck('requirement_type_id')
            ->all();

        $program->requirements()
            ->whereNotIn('requirement_type_id', array_keys($requirements))
            ->whereNotIn('requirement_type_id', $answered)
            ->delete();

        foreach ($requirements as $typeId => $attributes) {
            $program->requirements()->updateOrCreate(
                ['requirement_type_id' => $typeId],
                $attributes,
            );
        }
    }
}
