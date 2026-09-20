<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $applications = Auth::user()
            ->applications()
            ->with('program')
            ->latest('submission_date')
            ->get();

        return view('student.dashboard', compact('applications'));
    }
}
