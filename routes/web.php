<?php

use App\Http\Controllers\Coordinator\ApplicationController as CoordinatorApplicationController;
use App\Http\Controllers\Coordinator\DashboardController as CoordinatorDashboardController;
use App\Http\Controllers\Coordinator\DisbursementController;
use App\Http\Controllers\Coordinator\ProgramController as CoordinatorProgramController;
use App\Http\Controllers\Coordinator\StudentController as CoordinatorStudentController;
use App\Http\Controllers\Coordinator\UserController as CoordinatorUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\Reviewer\ApplicationController as ReviewerApplicationController;
use App\Http\Controllers\Reviewer\DashboardController as ReviewerDashboardController;
use App\Http\Controllers\Reviewer\ReviewController;
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProgramController::class, 'index'])->name('home');
Route::get('/scholarships/{program}', [ProgramController::class, 'show'])->name('scholarships.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/programs/{program}/apply', [StudentApplicationController::class, 'create'])->name('applications.create');
        Route::post('/applications', [StudentApplicationController::class, 'store'])->name('applications.store');
        Route::get('/applications/{application}', [StudentApplicationController::class, 'show'])->name('applications.show');
    });

Route::middleware(['auth', 'verified', 'role:reviewer'])
    ->prefix('reviewer')
    ->name('reviewer.')
    ->group(function () {
        Route::get('/dashboard', [ReviewerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/applications/{application}/claim', [ReviewerApplicationController::class, 'claim'])->name('applications.claim');
        Route::get('/applications/{application}', [ReviewerApplicationController::class, 'show'])->name('applications.show');
        Route::post('/applications/{application}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
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
        Route::get('/users', [CoordinatorUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [CoordinatorUserController::class, 'create'])->name('users.create');
        Route::post('/users', [CoordinatorUserController::class, 'store'])->name('users.store');
    });

require __DIR__.'/auth.php';
