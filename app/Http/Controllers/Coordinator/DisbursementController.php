<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisbursementRequest;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;

class DisbursementController extends Controller
{
    public function store(StoreDisbursementRequest $request, Application $application): RedirectResponse
    {
        $application->disbursements()->create($request->validated());

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', 'Disbursement recorded.');
    }
}
