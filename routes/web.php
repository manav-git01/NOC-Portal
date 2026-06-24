<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\InternshipApplicationController;
use App\Http\Controllers\Faculty\ApprovalController;
use App\Http\Controllers\HigherFaculty\NocController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BatchDirectoryController;
use App\Http\Controllers\Admin\StudentDirectoryController;
use App\Http\Controllers\Admin\FacultyDirectoryController;
use App\Http\Controllers\Admin\GuideAssignmentController;
use App\Http\Controllers\Admin\AuthorityManagementController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\AuditLogController;
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

    // Student Directory (Module 1)
    Route::get('/student-directory', [StudentDirectoryController::class, 'index'])->name('student-directory.index');
    Route::post('/student-directory', [StudentDirectoryController::class, 'store'])->name('student-directory.store');
    Route::put('/student-directory/{user}', [StudentDirectoryController::class, 'update'])->name('student-directory.update');
    Route::delete('/student-directory/{user}', [StudentDirectoryController::class, 'destroy'])->name('student-directory.destroy');
    Route::post('/student-directory/import', [StudentDirectoryController::class, 'import'])->name('student-directory.import');
    Route::post('/student-directory/{user}/assign-guide', [StudentDirectoryController::class, 'assignGuide'])->name('student-directory.assign-guide');
    Route::post('/student-directory/{user}/remove-guide', [StudentDirectoryController::class, 'removeGuide'])->name('student-directory.remove-guide');
    Route::post('/student-directory/{user}/move-batch', [StudentDirectoryController::class, 'moveBatch'])->name('student-directory.move-batch');

    // Faculty Directory (Module 2)
    Route::get('/faculty-directory', [FacultyDirectoryController::class, 'index'])->name('faculty-directory.index');
    Route::post('/faculty-directory', [FacultyDirectoryController::class, 'store'])->name('faculty-directory.store');
    Route::put('/faculty-directory/{user}', [FacultyDirectoryController::class, 'update'])->name('faculty-directory.update');
    Route::post('/faculty-directory/{user}/deactivate', [FacultyDirectoryController::class, 'deactivate'])->name('faculty-directory.deactivate');
    Route::delete('/faculty-directory/{user}', [FacultyDirectoryController::class, 'destroy'])->name('faculty-directory.destroy');

    // Guide Assignment Center (Module 3 & 4)
    Route::get('/guide-assignments', [GuideAssignmentController::class, 'index'])->name('guide-assignments.index');
    Route::post('/guide-assignments/assign', [GuideAssignmentController::class, 'assign'])->name('guide-assignments.assign');
    Route::post('/guide-assignments/release/{user}', [GuideAssignmentController::class, 'release'])->name('guide-assignments.release');
    Route::post('/guide-assignments/bulk-assign', [GuideAssignmentController::class, 'bulkAssign'])->name('guide-assignments.bulk-assign');

    // Authority Management (Module 6)
    Route::get('/authority-management', [AuthorityManagementController::class, 'index'])->name('authority-management.index');
    Route::put('/authority-management/{user}', [AuthorityManagementController::class, 'update'])->name('authority-management.update');

    // Account Management (Module 9)
    Route::get('/account-management', [AccountManagementController::class, 'index'])->name('account-management.index');
    Route::post('/account-management/{user}/activate', [AccountManagementController::class, 'activate'])->name('account-management.activate');
    Route::post('/account-management/{user}/deactivate', [AccountManagementController::class, 'deactivate'])->name('account-management.deactivate');

    // Audit Logs (Module 10)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

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

    // Faculty Authority Management
    Route::put('/faculty/{user}/authority', [AdminDashboardController::class, 'updateAuthority'])->name('faculty.update-authority');
    
    // Batch Guide Reassignment
    Route::post('/batches/{batch}/reassign-guide', [AdminDashboardController::class, 'reassignBatchGuide'])->name('batches.reassign-guide');

    // Batch Directory management
    Route::get('/batches/{batch}', [BatchDirectoryController::class, 'show'])->name('batches.show');
    Route::post('/students/{student}/change-batch', [BatchDirectoryController::class, 'updateStudentBatch'])->name('students.change-batch');
    Route::post('/students/{student}/change-guide', [BatchDirectoryController::class, 'updateStudentGuide'])->name('students.change-guide');
    Route::post('/batches/{batch}/change-guide', [BatchDirectoryController::class, 'updateBatchGuide'])->name('batches.change-guide');
    Route::post('/batches/bulk-transfer', [BatchDirectoryController::class, 'bulkTransfer'])->name('batches.bulk-transfer');

    // System Diagnostics (AJAX)
    Route::get('/system-diagnostics', [AdminDashboardController::class, 'systemDiagnostics'])->name('system-diagnostics');
});

require __DIR__.'/auth.php';
