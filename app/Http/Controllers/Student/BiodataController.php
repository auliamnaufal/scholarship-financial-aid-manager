<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBiodataRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The student's own biodata: academic details, contact, family circumstances
 * and the bank account an award is paid into. Separate from Breeze's profile
 * page, which handles the login credentials.
 */
class BiodataController extends Controller
{
    public function edit(): View
    {
        return view('student.biodata.edit', [
            'profile' => Auth::user()->studentProfile,
        ]);
    }

    public function update(UpdateBiodataRequest $request): RedirectResponse
    {
        Auth::user()->studentProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $request->validated(),
        );

        return redirect()
            ->route('student.biodata.edit')
            ->with('status', 'Biodata saved.');
    }
}
