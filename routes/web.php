<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\InternshipApplicationController;
use App\Http\Controllers\Faculty\ApprovalController;
use App\Http\Controllers\HigherFaculty\NocController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Serve storage files without symbolic link (Fixed 403 Forbidden error)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    if (file_exists($fullPath) && !is_dir($fullPath)) {
        return response()->file($fullPath);
    }
    
    abort(404);
})->where('path', '.*');

// Dashboard - Role-based routing
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/applications/create', [InternshipApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [InternshipApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [InternshipApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/request-noc', [InternshipApplicationController::class, 'requestNoc'])->name('applications.request-noc');
    Route::get('/applications/{application}/download-noc', [InternshipApplicationController::class, 'downloadNoc'])->name('applications.download-noc');
});

// Faculty routes
Route::middleware(['auth', 'role:faculty'])->prefix('faculty')->name('faculty.')->group(function () {
    Route::get('/applications/{application}', [ApprovalController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/approve', [ApprovalController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [ApprovalController::class, 'reject'])->name('applications.reject');
});

// Higher Faculty routes
Route::middleware(['auth', 'role:higher_faculty'])->prefix('higher-faculty')->name('higher-faculty.')->group(function () {
    Route::get('/applications/{application}', [NocController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/approve', [NocController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [NocController::class, 'reject'])->name('applications.reject');
    Route::get('/applications/{application}/download-noc', [NocController::class, 'downloadNoc'])->name('applications.download-noc');
});

require __DIR__.'/auth.php';
