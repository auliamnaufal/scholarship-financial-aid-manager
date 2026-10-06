<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Disbursement;
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

        $applications = Application::query()->whereIn('program_id', $programs->pluck('id'));

        return view('coordinator.dashboard', [
            'programs' => $programs,
            'stats' => [
                'programs' => $programs->count(),
                'awaiting' => (clone $applications)->where('status', ApplicationStatus::UnderReview)->count(),
                'recipients' => (clone $applications)->where('status', ApplicationStatus::Approved)->count(),
                'disbursed' => Disbursement::whereIn('application_id', (clone $applications)->select('id'))->sum('amount'),
            ],
        ]);
    }
}
