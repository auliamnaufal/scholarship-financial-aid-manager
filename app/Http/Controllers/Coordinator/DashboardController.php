<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $programs = Auth::user()
            ->programsManaged()
            ->withCount('applications')
            ->latest()
            ->get();

        return view('coordinator.dashboard', compact('programs'));
    }
}
