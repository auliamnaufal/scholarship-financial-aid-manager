<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Program::class);

        return view('coordinator.programs.create');
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = Auth::user()->programsManaged()->create($request->validated());

        return redirect()
            ->route('coordinator.dashboard')
            ->with('status', "Program \"{$program->name}\" created.");
    }

    public function edit(Program $program): View
    {
        $this->authorize('update', $program);

        return view('coordinator.programs.edit', compact('program'));
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        $program->update($request->validated());

        return redirect()
            ->route('coordinator.dashboard')
            ->with('status', "Program \"{$program->name}\" updated.");
    }
}
