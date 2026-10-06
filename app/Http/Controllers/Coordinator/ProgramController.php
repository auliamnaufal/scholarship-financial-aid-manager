<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use App\Models\RequirementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Program::class);

        $show = $request->string('show')->toString();

        $programs = Auth::user()->programsManaged()
            ->withTrashed()
            ->when($show === 'active', fn ($query) => $query->whereNull('deleted_at'))
            ->when($show === 'archived', fn ($query) => $query->whereNotNull('deleted_at'))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->string('q')).'%';

                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('funding_source', 'like', $term));
            })
            ->withCount('applications')
            ->orderByRaw('deleted_at is not null')
            ->orderBy('application_deadline')
            ->paginate(10)
            ->withQueryString();

        return view('coordinator.programs.index', [
            'programs' => $programs,
            'show' => $show,
            'type' => $request->string('type')->toString(),
        ]);
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
            ->with('status', __('Program ":name" created.', ['name' => $program->name]));
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
            ->with('status', __('Program ":name" updated.', ['name' => $program->name]));
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
            ->with('status', __('Program ":name" archived.', ['name' => $program->name]));
    }

    public function restore(int $program): RedirectResponse
    {
        $program = Program::withTrashed()->findOrFail($program);

        $this->authorize('restore', $program);

        $program->restore();

        return redirect()
            ->route('coordinator.programs.index')
            ->with('status', __('Program ":name" restored.', ['name' => $program->name]));
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
