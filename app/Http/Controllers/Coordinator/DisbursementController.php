<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisbursementRequest;
use App\Models\Application;
use App\Models\Disbursement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DisbursementController extends Controller
{
    public function store(DisbursementRequest $request, Application $application): RedirectResponse
    {
        $application->disbursements()->create($request->validated());

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', __('Disbursement recorded.'));
    }

    public function edit(Application $application, Disbursement $disbursement): View
    {
        $this->authorize('update', $disbursement);

        return view('coordinator.disbursements.edit', compact('application', 'disbursement'));
    }

    public function update(DisbursementRequest $request, Application $application, Disbursement $disbursement): RedirectResponse
    {
        $disbursement->update($request->validated());

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', __('Disbursement updated.'));
    }

    /**
     * A payment that was entered by mistake. Nothing hangs off a disbursement,
     * and what is still owed is worked out from the rows that remain, so
     * removing one simply puts its amount back among what is still to be paid.
     */
    public function destroy(Application $application, Disbursement $disbursement): RedirectResponse
    {
        $this->authorize('delete', $disbursement);

        $disbursement->delete();

        return redirect()
            ->route('coordinator.applications.show', $application)
            ->with('status', __('Disbursement deleted.'));
    }
}
