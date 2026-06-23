<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\InternshipApplicationController;
use App\Http\Controllers\Faculty\ApprovalController;
use App\Http\Controllers\HigherFaculty\NocController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BatchDirectoryController;
use App\Http\Controllers\Admin\MentorMappingArchiveController;
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

Route::middleware('auth')->group(function () {
    Route::get('/select-dashboard', [DashboardController::class, 'showSelectDashboard'])->name('select-dashboard');
    Route::get('/switch-dashboard/{dashboard}', [DashboardController::class, 'switchDashboard'])->name('switch-dashboard');
});

use App\Http\Controllers\Auth\PasswordChangeController;

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Global settings & Change Password routes
    Route::get('/profile/settings', [ProfileController::class, 'editSettings'])->name('profile.settings');
    Route::put('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');
    Route::get('/profile/change-password', [ProfileController::class, 'editPassword'])->name('profile.change-password');
    Route::put('/profile/change-password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('profile.password.update');

    // Forced password change routes
    Route::get('/password/change', [PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');
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
    Route::middleware('faculty_approval')->group(function () {
        Route::get('/approval-dashboard', [DashboardController::class, 'approvalDashboard'])->name('approval-dashboard');
        Route::post('/applications/{application}/approve', [ApprovalController::class, 'approve'])->name('applications.approve');
        Route::post('/applications/{application}/reject', [ApprovalController::class, 'reject'])->name('applications.reject');
    });

    Route::get('/guide-dashboard', [App\Http\Controllers\Faculty\GuideDashboardController::class, 'index'])
        ->middleware('guide_only')
        ->name('guide-dashboard');

    Route::get('/guide-dashboard/application/{application}', [App\Http\Controllers\Faculty\GuideDashboardController::class, 'showApplication'])
        ->middleware('guide_only')
        ->name('guide.application-details');

    Route::get('/applications/{application}', [ApprovalController::class, 'show'])->name('applications.show');
});

// Higher Faculty routes
Route::middleware(['auth', 'role:higher_faculty', 'higher_faculty_approval'])->prefix('higher-faculty')->name('higher-faculty.')->group(function () {
    Route::get('/noc-dashboard', [DashboardController::class, 'nocDashboard'])->name('noc-dashboard');
    Route::get('/applications/{application}', [NocController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/approve', [NocController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [NocController::class, 'reject'])->name('applications.reject');
    Route::get('/applications/{application}/download-noc', [NocController::class, 'downloadNoc'])->name('applications.download-noc');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Batches CRUD
    Route::post('/batches', [AdminDashboardController::class, 'storeBatch'])->name('batches.store');
    Route::put('/batches/{batch}', [AdminDashboardController::class, 'updateBatch'])->name('batches.update');
    Route::delete('/batches/{batch}', [AdminDashboardController::class, 'destroyBatch'])->name('batches.destroy');

    // Student CRUD & Import
    Route::post('/students', [AdminDashboardController::class, 'storeStudent'])->name('students.store');
    Route::put('/students/{user}', [AdminDashboardController::class, 'updateStudent'])->name('students.update');
    Route::delete('/students/{user}', [AdminDashboardController::class, 'destroyStudent'])->name('students.destroy');
    Route::post('/students/import', [AdminDashboardController::class, 'importStudents'])->name('students.import');

    // Faculty CRUD & Delete
    Route::delete('/faculty/{user}', [AdminDashboardController::class, 'destroyFaculty'])->name('faculty.destroy');

    // Mentor Mapping Import & Preview
    Route::post('/mentor-mapping/preview', [AdminDashboardController::class, 'previewMentorMapping'])->name('mentor-mapping.preview');
    Route::post('/mentor-mapping/confirm', [AdminDashboardController::class, 'confirmMentorMapping'])->name('mentor-mapping.confirm');
    Route::get('/mentor-mapping/download-report', [AdminDashboardController::class, 'downloadCreatedAccountsReport'])->name('mentor-mapping.download-report');

    // Faculty Authority Management
    Route::put('/faculty/{user}/authority', [AdminDashboardController::class, 'updateAuthority'])->name('faculty.update-authority');
    
    // Batch Guide Reassignment
    Route::post('/batches/{batch}/reassign-guide', [AdminDashboardController::class, 'reassignBatchGuide'])->name('batches.reassign-guide');

    // Batch Directory management
    Route::get('/batches/{batch}', [BatchDirectoryController::class, 'show'])->name('batches.show');
    Route::post('/students/{student}/change-batch', [BatchDirectoryController::class, 'updateStudentBatch'])->name('students.change-batch');
    Route::post('/students/{student}/change-guide', [BatchDirectoryController::class, 'updateStudentGuide'])->name('students.change-guide');
    Route::post('/batches/{batch}/change-guide', [BatchDirectoryController::class, 'updateBatchGuide'])->name('batches.change-guide');

    // Mentor Mapping Archive management
    Route::get('/mentor-mapping/archives', [MentorMappingArchiveController::class, 'index'])->name('mentor-mapping.archives');
    Route::get('/mentor-mapping/archives/{archive}', [MentorMappingArchiveController::class, 'show'])->name('mentor-mapping.archives.show');
    Route::post('/mentor-mapping/archives/{archive}/restore', [MentorMappingArchiveController::class, 'restore'])->name('mentor-mapping.archives.restore');
    Route::get('/mentor-mapping/archives/{archive}/download', [MentorMappingArchiveController::class, 'downloadReport'])->name('mentor-mapping.archives.download');

    // System Diagnostics (AJAX)
    Route::get('/system-diagnostics', [AdminDashboardController::class, 'systemDiagnostics'])->name('system-diagnostics');
});

require __DIR__.'/auth.php';
