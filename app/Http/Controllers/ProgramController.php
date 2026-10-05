<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

/**
 * Public, unauthenticated scholarship browsing (listing + detail).
 */
class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::query()
            ->where('application_deadline', '>=', Date::today())
            ->withCount('requirements')
            ->orderBy('application_deadline')
            ->get();

        return view('programs.index', compact('programs'));
    }

    public function show(Program $program): View
    {
        $program->load('requirements.requirementType');

        return view('programs.show', compact('program'));
    }
}
