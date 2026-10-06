<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequirementTypeRequest;
use App\Models\RequirementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The menu of things a scholarship can ask applicants for. Coordinators build
 * it here and then tick items from it on each scholarship.
 */
class RequirementTypeController extends Controller
{
    public function index(): View
    {
        $types = RequirementType::query()
            ->withCount(['programRequirements', 'applicationDocuments'])
            ->orderBy('name')
            ->get();

        return view('coordinator.requirement-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('coordinator.requirement-types.create');
    }

    public function store(RequirementTypeRequest $request): RedirectResponse
    {
        $type = RequirementType::create($request->validated() + [
            'slug' => RequirementType::uniqueSlug($request->validated('name')),
        ]);

        return redirect()
            ->route('coordinator.requirement-types.index')
            ->with('status', __('Requirement ":name" created.', ['name' => $type->name]));
    }

    public function edit(RequirementType $requirementType): View
    {
        return view('coordinator.requirement-types.edit', ['type' => $requirementType]);
    }

    /** The slug stays as it was: seeders and links may refer to it. */
    public function update(RequirementTypeRequest $request, RequirementType $requirementType): RedirectResponse
    {
        $requirementType->update($request->validated());

        return redirect()
            ->route('coordinator.requirement-types.index')
            ->with('status', __('Requirement ":name" updated.', ['name' => $requirementType->name]));
    }

    /**
     * Deleting a type would also delete every scholarship's line for it and
     * every applicant's answer, since both cascade. So only a type nobody has
     * used can be removed.
     */
    public function destroy(RequirementType $requirementType): RedirectResponse
    {
        if ($requirementType->isInUse()) {
            return back()->withErrors([
                'requirement_type' => __('":name" is already used by a scholarship, so it cannot be deleted.', ['name' => $requirementType->name]),
            ]);
        }

        $requirementType->delete();

        return redirect()
            ->route('coordinator.requirement-types.index')
            ->with('status', __('Requirement ":name" deleted.', ['name' => $requirementType->name]));
    }
}
