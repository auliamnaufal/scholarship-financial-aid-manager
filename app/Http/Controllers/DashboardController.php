<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        $isCoordinator = $user->hasRole('coordinator');
        $isReviewer = $user->hasRole('reviewer');
        $isStudent = $user->hasRole('student');

        if ($isCoordinator && $isReviewer) {
            return view('dashboard-picker');
        }

        if ($isStudent) {
            return redirect()->route('student.dashboard');
        }

        if ($isCoordinator) {
            return redirect()->route('coordinator.dashboard');
        }

        if ($isReviewer) {
            return redirect()->route('reviewer.dashboard');
        }

        return view('dashboard');
    }
}
