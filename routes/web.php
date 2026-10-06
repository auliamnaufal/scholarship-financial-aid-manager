<?php

use App\Http\Controllers\Coordinator\ApplicationController as CoordinatorApplicationController;
use App\Http\Controllers\Coordinator\DashboardController as CoordinatorDashboardController;
use App\Http\Controllers\Coordinator\DisbursementController;
use App\Http\Controllers\Coordinator\ProgramController as CoordinatorProgramController;
use App\Http\Controllers\Coordinator\RequirementTypeController;
use App\Http\Controllers\Coordinator\StudentController as CoordinatorStudentController;
use App\Http\Controllers\Coordinator\UserController as CoordinatorUserController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\Reviewer\ApplicationController as ReviewerApplicationController;
use App\Http\Controllers\Reviewer\DashboardController as ReviewerDashboardController;
use App\Http\Controllers\Reviewer\ReviewController;
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Student\BiodataController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProgramController::class, 'index'])->name('home');
Route::get('/scholarships/{program}', [ProgramController::class, 'show'])->name('scholarships.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Uploads are private; the controller checks the application's own policy
    // before streaming one, so students, reviewers and the owning coordinator
    // all reach them through the same gate.
    Route::get('/documents/{document}', [ApplicationDocumentController::class, 'show'])->name('documents.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/biodata', [BiodataController::class, 'edit'])->name('biodata.edit');
        Route::put('/biodata', [BiodataController::class, 'update'])->name('biodata.update');
        Route::get('/programs/{program}/apply', [StudentApplicationController::class, 'create'])->name('applications.create');
        Route::post('/applications', [StudentApplicationController::class, 'store'])->name('applications.store');
        Route::get('/applications/{application}', [StudentApplicationController::class, 'show'])->name('applications.show');
        Route::post('/applications/{application}/cancel', [StudentApplicationController::class, 'cancel'])->name('applications.cancel');
    });

Route::middleware(['auth', 'verified', 'role:reviewer|coordinator'])
    ->prefix('reviewer')
    ->name('reviewer.')
    ->group(function () {
        Route::get('/dashboard', [ReviewerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/applications/{application}/claim', [ReviewerApplicationController::class, 'claim'])->name('applications.claim');
        Route::get('/applications/{application}', [ReviewerApplicationController::class, 'show'])->name('applications.show');
        Route::post('/applications/{application}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    });

Route::middleware(['auth', 'verified', 'role:coordinator'])
    ->prefix('coordinator')
    ->name('coordinator.')
    ->group(function () {
        Route::get('/dashboard', [CoordinatorDashboardController::class, 'index'])->name('dashboard');

        Route::get('/programs', [CoordinatorProgramController::class, 'index'])->name('programs.index');
        Route::get('/programs/create', [CoordinatorProgramController::class, 'create'])->name('programs.create');
        Route::post('/programs', [CoordinatorProgramController::class, 'store'])->name('programs.store');
        Route::get('/programs/{program}/edit', [CoordinatorProgramController::class, 'edit'])->name('programs.edit');
        Route::put('/programs/{program}', [CoordinatorProgramController::class, 'update'])->name('programs.update');
        Route::delete('/programs/{program}', [CoordinatorProgramController::class, 'destroy'])->name('programs.destroy');
        Route::post('/programs/{program}/restore', [CoordinatorProgramController::class, 'restore'])->name('programs.restore');

        Route::get('/students', [CoordinatorStudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [CoordinatorStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [CoordinatorStudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [CoordinatorStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [CoordinatorStudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [CoordinatorStudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/{student}/restore', [CoordinatorStudentController::class, 'restore'])->name('students.restore');
        Route::get('/applications', [CoordinatorApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [CoordinatorApplicationController::class, 'show'])->name('applications.show');
        Route::post('/applications/{application}/approve', [CoordinatorApplicationController::class, 'approve'])->name('applications.approve');
        Route::post('/applications/{application}/reject', [CoordinatorApplicationController::class, 'reject'])->name('applications.reject');
        Route::post('/applications/{application}/disbursements', [DisbursementController::class, 'store'])->name('disbursements.store');
        // scopeBindings: the disbursement must belong to the application in the URL.
        Route::get('/applications/{application}/disbursements/{disbursement}/edit', [DisbursementController::class, 'edit'])->scopeBindings()->name('disbursements.edit');
        Route::put('/applications/{application}/disbursements/{disbursement}', [DisbursementController::class, 'update'])->scopeBindings()->name('disbursements.update');
        Route::delete('/applications/{application}/disbursements/{disbursement}', [DisbursementController::class, 'destroy'])->scopeBindings()->name('disbursements.destroy');
        Route::get('/requirement-types', [RequirementTypeController::class, 'index'])->name('requirement-types.index');
        Route::get('/requirement-types/create', [RequirementTypeController::class, 'create'])->name('requirement-types.create');
        Route::post('/requirement-types', [RequirementTypeController::class, 'store'])->name('requirement-types.store');
        Route::get('/requirement-types/{requirement_type}/edit', [RequirementTypeController::class, 'edit'])->name('requirement-types.edit');
        Route::put('/requirement-types/{requirement_type}', [RequirementTypeController::class, 'update'])->name('requirement-types.update');
        Route::delete('/requirement-types/{requirement_type}', [RequirementTypeController::class, 'destroy'])->name('requirement-types.destroy');
        Route::get('/users', [CoordinatorUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [CoordinatorUserController::class, 'create'])->name('users.create');
        Route::post('/users', [CoordinatorUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [CoordinatorUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [CoordinatorUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [CoordinatorUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/restore', [CoordinatorUserController::class, 'restore'])->name('users.restore');
    });

require __DIR__.'/auth.php';
