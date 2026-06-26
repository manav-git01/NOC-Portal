@extends('layouts.app')

@section('content')
@php
    $activeTab = $activeTab ?? request('tab', 'statistics');
@endphp
<div class="min-h-screen bg-gray-50 pb-12" x-data="{ 
    activeTab: '{{ $activeTab }}',
    showBatchModal: false,
    showStudentModal: false,
    showFacultyModal: false,
    showViewBatchStudentsModal: false,
    showDeleteStudentModal: false,
    showDeleteFacultyModal: false,
    showMoveBatchModal: false,
    showAssignGuideModal: false,
    modalMode: 'create',
    
    // Form states
    batchForm: { id: null, name: '' },
    studentForm: { id: null, enrollment_number: '', name: '', email: '', department: '', semester: 1, batch_id: '', guide_id: '', password: '' },
    facultyForm: { id: null, faculty_id: '', name: '', email: '', department: '', designation: '' },
    moveBatchForm: { student_id: null, student_name: '', batch_id: '' },
    assignGuideForm: { student_id: null, student_name: '', guide_id: '' },
    
    // Deletion states
    deleteStudentData: { id: null, name: '', enrollment_number: '' },
    deleteStudentConfirmationInput: '',
    deleteFacultyData: { id: null, name: '', email: '' },
    deleteFacultyConfirmationInput: '',

    // View drilldown details
    viewBatch: { name: '', students_count: 0, guides_count: 0, pending_apps: 0, approved_apps: 0, noc_count: 0, students: [] },

    // Helpers
    openEditBatch(batch) {
        this.modalMode = 'edit';
        this.batchForm = { id: batch.id, name: batch.name };
        this.showBatchModal = true;
    },
    openCreateBatch() {
        this.modalMode = 'create';
        this.batchForm = { id: null, name: '' };
        this.showBatchModal = true;
    },
    openEditStudent(stud) {
        this.modalMode = 'edit';
        this.studentForm = {
            id: stud.id,
            enrollment_number: stud.enrollment_number || '',
            name: stud.name,
            email: stud.email,
            department: stud.department || '',
            semester: stud.semester || 1,
            batch_id: stud.batch_id || '',
            guide_id: stud.guide_id || '',
            password: ''
        };
        this.showStudentModal = true;
    },
    openCreateStudent() {
        this.modalMode = 'create';
        this.studentForm = { id: null, enrollment_number: '', name: '', email: '', department: '', semester: 1, batch_id: '', guide_id: '', password: '' };
        this.showStudentModal = true;
    },
    openEditFaculty(fac) {
        this.modalMode = 'edit';
        this.facultyForm = {
            id: fac.id,
            faculty_id: fac.faculty_id || '',
            name: fac.name,
            email: fac.email,
            department: fac.department || '',
            designation: fac.designation || ''
        };
        this.showFacultyModal = true;
    },
    openCreateFaculty() {
        this.modalMode = 'create';
        this.facultyForm = { id: null, faculty_id: '', name: '', email: '', department: '', designation: '' };
        this.showFacultyModal = true;
    },
    openDeleteStudent(stud) {
        this.deleteStudentData = { id: stud.id, name: stud.name, enrollment_number: stud.enrollment_number || '' };
        this.deleteStudentConfirmationInput = '';
        this.showDeleteStudentModal = true;
    },
    openDeleteFaculty(fac) {
        this.deleteFacultyData = { id: fac.id, name: fac.name, email: fac.email || '' };
        this.deleteFacultyConfirmationInput = '';
        this.showDeleteFacultyModal = true;
    },
    openMoveBatch(stud) {
        this.moveBatchForm = { student_id: stud.id, student_name: stud.name, batch_id: stud.batch_id || '' };
        this.showMoveBatchModal = true;
    },
    openAssignGuide(stud) {
        this.assignGuideForm = { student_id: stud.id, student_name: stud.name, guide_id: stud.guide_id || '' };
        this.showAssignGuideModal = true;
    }
}">

    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm mb-8 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center shadow-md">
                        <i class="fas fa-user-shield text-white text-lg animate-pulse"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-indigo-600 font-semibold uppercase tracking-wider">System Administrator</p>
                    </div>
                </div>

                <div class="hidden md:block">
                    <span class="text-gray-800 font-bold text-lg tracking-wide uppercase">Internship NOC Portal</span>
                </div>

                <div class="flex items-center space-x-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center space-x-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-150 font-medium text-sm shadow-sm">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Flash messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm flex items-center space-x-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-emerald-800 font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-sm flex items-center space-x-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-rose-800 font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-sm">
                <div class="flex items-center space-x-3 mb-2">
                    <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                    <span class="text-rose-800 font-bold">Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-rose-700 text-xs font-semibold space-y-1 ml-6">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif



        <!-- General Stats cards visible contextually -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Students -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-cyan-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-graduate text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-cyan-600 text-xs font-bold uppercase tracking-wider">Total Students</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalStudents ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Faculty -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-blue-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chalkboard-teacher text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-blue-600 text-xs font-bold uppercase tracking-wider">Total Faculty</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalFaculty ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Guides -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-teal-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-teal-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-tie text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-teal-600 text-xs font-bold uppercase tracking-wider">Active Guides</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalGuides ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Batches -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-purple-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-purple-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-layer-group text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-purple-600 text-xs font-bold uppercase tracking-wider">Batches</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalBatches ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Applications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-cyan-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-signature text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-cyan-600 text-xs font-bold uppercase tracking-wider">Total Applications</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalApplications ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-orange-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-orange-600 text-xs font-bold uppercase tracking-wider">Pending Review</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $pendingApplications ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Approved -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-green-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-green-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-green-600 text-xs font-bold uppercase tracking-wider">NOC Generated</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $approvedApplications ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- NOCs / Rejected -->
            <!-- Wait! Let's check the photo: "REJECTED" is the 4th card, but here we have NOCs Generated and Rejected. Let's make NOCs Generated and Rejected both match the design. Wait, does the admin dashboard have a Rejected card or a NOCs Generated card? Let's check:
            Wait, let's look at the original code. It has NOCs Generated but it didn't have Rejected. Let's keep NOCs Generated, and maybe add Rejected if needed, or just style NOCs Generated to look like the NOC Generated/Green or Pink card. Let's see: The original code has "NOCs Generated" which is pink. Let's keep NOCs Generated as pink, or rose/red for Rejected if they want it. Wait! The admin controller returns $generatedNocs but it doesn't calculate Rejected Applications? Let's check:
            In AdminDashboardController.php line 42:
            $pendingApplications = InternshipApplication::whereIn('status', ['pending', 'pending_higher'])->count();
            $approvedApplications = ...
            Wait, does it have rejected applications?
            Ah! Let's check line 40-55 of AdminDashboardController.php:
            $totalApplications = InternshipApplication::count();
            $pendingApplications = ...
            $approvedApplications = ...
            Wait, is there a rejected applications count in the controller?
            Let's search for "rejected" in AdminDashboardController.php. -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-pink-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-pink-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-certificate text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-pink-600 text-xs font-bold uppercase tracking-wider">NOCs Generated</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $generatedNocs ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (10 modules ERP navigation) -->
        <div class="flex flex-nowrap overflow-x-auto gap-1.5 mb-8 bg-gray-100/80 p-1.5 rounded-2xl border border-gray-200 whitespace-nowrap" style="scrollbar-width: none; -ms-overflow-style: none;">
            <a href="{{ route('admin.dashboard', ['tab' => 'statistics']) }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ ($activeTab === 'statistics' || $activeTab === 'dashboard') ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-chart-line text-[13px]"></i>
                <span>Statistics</span>
            </a>

            <a href="{{ route('admin.student-directory.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ $activeTab === 'student_directory' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-user-graduate text-[13px]"></i>
                <span>Student Directory</span>
            </a>

            <a href="{{ route('admin.faculty-directory.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ $activeTab === 'faculty_directory' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-chalkboard-teacher text-[13px]"></i>
                <span>Faculty Directory</span>
            </a>

            <a href="{{ route('admin.guide-assignments.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ $activeTab === 'guide_assignments' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-user-check text-[13px]"></i>
                <span>Guide Assignments</span>
            </a>

            <a href="{{ route('admin.dashboard', ['tab' => 'batches']) }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ $activeTab === 'batches' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-layer-group text-[13px]"></i>
                <span>Batch Directory</span>
            </a>

            <a href="{{ route('admin.authority-management.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ $activeTab === 'authority_management' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-users-cog text-[13px]"></i>
                <span>Authority Management</span>
            </a>

            <a href="{{ route('admin.audit-logs.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ $activeTab === 'audit_logs' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-history text-[13px]"></i>
                <span>Audit Logs</span>
            </a>

            <a href="{{ route('admin.dashboard', ['tab' => 'system_diagnostics']) }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-200 flex-shrink-0 {{ $activeTab === 'system_diagnostics' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-white/60 hover:text-gray-950' }}">
                <i class="fas fa-diagnoses text-[13px]"></i>
                <span>Diagnostics</span>
            </a>
        </div>


        <!-- ============================================== -->
        <!-- TAB CONTENTS -->
        <!-- ============================================== -->

        <!-- TAB 1: STATISTICS / LANDING -->
        <div x-show="activeTab === 'statistics' || activeTab === 'dashboard'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-50 rounded-full blur-3xl opacity-50 -z-10"></div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Centralized ERP Dashboard</h3>
                <p class="text-xs text-gray-400 font-semibold mb-6">Welcome to the NOC Portal Control Center. Manage directories, assign guides, monitor accounts and track system changes.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs hover:border-indigo-200 hover:shadow-sm transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider">Application Pipeline</h4>
                                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-500 font-semibold">Total Applications</span>
                                    <span class="font-bold text-xs text-gray-800 bg-gray-100 px-2 py-0.5 rounded-full">{{ $totalApplications ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-500 font-semibold">Pending Review</span>
                                    <span class="font-bold text-xs text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ $pendingApplications ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-gray-500 font-semibold">Approved</span>
                                    <span class="font-bold text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $approvedApplications ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs hover:border-indigo-200 hover:shadow-sm transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider">User Distribution</h4>
                                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs"><i class="fas fa-users"></i></span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-500 font-semibold">Total Students</span>
                                    <span class="font-bold text-xs text-gray-800 bg-gray-100 px-2 py-0.5 rounded-full">{{ $totalStudents ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-500 font-semibold">Total Faculty</span>
                                    <span class="font-bold text-xs text-gray-800 bg-gray-100 px-2 py-0.5 rounded-full">{{ $totalFaculty ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-gray-500 font-semibold">Active Guides</span>
                                    <span class="font-bold text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ $totalGuides ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs hover:border-indigo-200 hover:shadow-sm transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider">System Metrics</h4>
                                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs"><i class="fas fa-layer-group"></i></span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                    <span class="text-xs text-gray-500 font-semibold">Total Batches</span>
                                    <span class="font-bold text-xs text-gray-800 bg-gray-100 px-2 py-0.5 rounded-full">{{ $totalBatches ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-gray-500 font-semibold">NOC Generated</span>
                                    <span class="font-bold text-xs text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full">{{ $generatedNocs ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: STUDENT MASTER DIRECTORY -->
        <div x-show="activeTab === 'student_directory'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-white p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-user-graduate text-sm"></i></span>
                            <h3 class="text-lg font-bold text-gray-900">Student Master Directory</h3>
                        </div>
                        <p class="text-xs text-gray-450 font-semibold mt-1.5">Query and filter student details, academic batches, assigned guides and accounts.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="openCreateStudent()" class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition shadow-xs">
                            <i class="fas fa-plus"></i>
                            <span>Add Student Manually</span>
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('admin.student-directory.index') }}" class="bg-gray-50/50 p-5 border-b border-gray-150 grid grid-cols-1 sm:grid-cols-5 gap-3.5">
                    <input type="hidden" name="tab" value="student_directory">
                    <div class="relative col-span-1 sm:col-span-2">
                        <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="Search by name, email, or enrollment..." class="w-full bg-white border border-gray-255 rounded-xl pl-9 pr-4 py-2.5 text-xs outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400 text-xs"></i>
                    </div>

                    <select name="batch_id" class="w-full bg-white border border-gray-255 rounded-xl py-2.5 px-3 text-xs outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="">All Batches</option>
                        @foreach($batches ?? [] as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>

                    <select name="guide_id" class="w-full bg-white border border-gray-255 rounded-xl py-2.5 px-3 text-xs outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="">All Guides</option>
                        @foreach($guides ?? [] as $g)
                            <option value="{{ $g->id }}" {{ request('guide_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex space-x-2">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl py-2.5 px-3 transition shadow-xs">Filter</button>
                        <a href="{{ route('admin.student-directory.index') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs rounded-xl py-2.5 px-3 transition flex items-center justify-center">Reset</a>
                    </div>
                </form>

                <!-- Students Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Enrollment Number</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Department & Sem</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Batch</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Assigned Guide</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-wider text-center">Account Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-xs">
                            @foreach($students ?? [] as $stud)
                                <tr class="hover:bg-indigo-50/10 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-950 font-mono tracking-wide">{{ $stud->enrollment_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $stud->name }}</div>
                                        <div class="text-xs text-gray-400 font-semibold font-mono mt-0.5">{{ $stud->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[10px]">{{ $stud->department ?? 'N/A' }}</span>
                                        <div class="text-[9px] text-gray-400 font-bold uppercase mt-1">Semester: {{ $stud->semester ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-650">{{ $stud->batch?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($stud->guide)
                                            <div class="flex items-center space-x-1.5 bg-indigo-50/50 border border-indigo-100/50 px-2.5 py-1 rounded-lg w-max">
                                                <span class="font-bold text-indigo-950 text-[11px]">{{ $stud->guide->name }}</span>
                                                @if($stud->is_locked)
                                                    <i class="fas fa-lock text-[9px] text-indigo-500" title="Locked Assignment"></i>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black tracking-wider uppercase border {{ $stud->account_status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-gray-50 text-gray-400 border-gray-150' }}">
                                            {{ $stud->account_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openEditStudent({{ json_encode($stud) }})" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition text-[11px]">Edit</button>
                                            <button @click="openMoveBatch({{ json_encode($stud) }})" class="bg-teal-50 text-teal-700 hover:bg-teal-150 px-2.5 py-1 rounded-lg transition text-[11px]">Move</button>
                                            <button @click="openAssignGuide({{ json_encode($stud) }})" class="bg-purple-50 text-purple-700 hover:bg-purple-150 px-2.5 py-1 rounded-lg transition text-[11px]">Assign</button>
                                            @if($stud->guide_id)
                                                <form action="{{ route('admin.student-directory.remove-guide', $stud->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-amber-50 text-amber-700 hover:bg-amber-100 px-2.5 py-1 rounded-lg transition text-[11px]">Release</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.student-directory.deactivate', $stud->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-slate-50 text-slate-700 hover:bg-slate-100 px-2.5 py-1 rounded-lg transition text-[11px]">
                                                    {{ $stud->account_status === 'active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <button @click="openDeleteStudent({{ json_encode($stud) }})" class="bg-rose-50 text-rose-700 hover:bg-rose-100 px-2.5 py-1 rounded-lg transition text-[11px]">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: FACULTY DIRECTORY -->
        <div x-show="activeTab === 'faculty_directory'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-white p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-chalkboard-teacher text-sm"></i></span>
                            <h3 class="text-lg font-bold text-gray-900">Faculty Directory</h3>
                        </div>
                        <p class="text-xs text-gray-450 font-semibold mt-1.5">Query and manage faculty profiles and configurations.</p>
                    </div>
                    <div>
                        <button @click="openCreateFaculty()" class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition shadow-xs">
                            <i class="fas fa-plus"></i>
                            <span>Add Faculty Manually</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Faculty ID</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Faculty Name</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Designation</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-wider text-center">Assigned Students</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-wider text-center">Account Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-xs">
                            @foreach($faculty ?? [] as $fac)
                                <tr class="hover:bg-indigo-50/10 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-955 font-mono tracking-wide">{{ $fac->faculty_id ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $fac->name }}</div>
                                        <div class="text-xs text-gray-400 font-semibold font-mono mt-0.5">{{ $fac->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">{{ $fac->department }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-550 font-semibold bg-gray-50/50 rounded-lg px-2.5 py-1 text-[11px] w-max">{{ $fac->designation }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-900">{{ $fac->students_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black tracking-wider uppercase border {{ $fac->account_status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-gray-50 text-gray-400 border-gray-150' }}">
                                            {{ $fac->account_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openEditFaculty({{ json_encode($fac) }})" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition text-[11px]">Edit</button>
                                            <form action="{{ route('admin.faculty-directory.deactivate', $fac->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-slate-50 text-slate-700 hover:bg-slate-100 px-2.5 py-1 rounded-lg transition text-[11px]">
                                                    {{ $fac->account_status === 'active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <button @click="openDeleteFaculty({{ json_encode($fac) }})" class="bg-rose-50 text-rose-700 hover:bg-rose-100 px-2.5 py-1 rounded-lg transition text-[11px]">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: GUIDE ASSIGNMENT CENTER -->
        <div x-show="activeTab === 'guide_assignments'" x-transition class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                 <!-- Left: Bulk Assignment Form -->
                 <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col justify-between">
                     <div>
                         <div class="flex items-center space-x-2 mb-2">
                             <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-user-check text-xs"></i></span>
                             <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Bulk Assign Guide</h3>
                         </div>
                         <p class="text-xs text-gray-450 font-semibold mb-4">Select students on the right and choose their new guide and batch configuration.</p>
                         
                         <form action="{{ route('admin.guide-assignments.bulk-assign') }}" method="POST" class="space-y-4"
                               x-data="{ selectedStudents: [] }"
                               @student-selection-changed.window="
                                   selectedStudents = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(el => {
                                       let id = el.value;
                                       let enroll = document.querySelector('#std-enroll-' + id)?.innerText || '';
                                       let name = document.querySelector('#std-name-' + id)?.innerText || '';
                                       return { id: id, enroll: enroll, name: name };
                                   });
                               ">
                             @csrf
                             <div>
                                 <label class="block text-xs font-bold text-gray-700 mb-1.5">Choose Guide</label>
                                 <select name="guide_id" required class="w-full border border-gray-250 bg-white rounded-xl py-2.5 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500">
                                     <option value="">-- Select Guide --</option>
                                     @foreach($guides ?? [] as $g)
                                         <option value="{{ $g->id }}">{{ $g->name }}</option>
                                     @endforeach
                                 </select>
     
                                 <label class="block text-xs font-bold text-gray-700 mb-1.5 mt-4">Choose Batch (Optional)</label>
                                 <select name="batch_id" class="w-full border border-gray-250 bg-white rounded-xl py-2.5 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500">
                                     <option value="">-- Select Batch --</option>
                                     @foreach($batches ?? [] as $b)
                                         <option value="{{ $b->id }}">{{ $b->name }}</option>
                                     @endforeach
                                 </select>
     
                                 <div class="mt-5 bg-gray-50/50 border border-gray-150 p-4 rounded-xl text-xs">
                                     <span class="font-bold text-gray-800 text-[11px] uppercase tracking-wider">Selected Students:</span>
                                     <div class="mt-2 max-h-32 overflow-y-auto space-y-1.5 font-mono">
                                         <template x-for="std in selectedStudents" :key="std.id">
                                             <div class="flex justify-between items-center py-1 border-b border-gray-100">
                                                 <span class="text-indigo-650 font-bold font-mono" x-text="std.enroll"></span>
                                                 <span class="text-gray-550 text-right truncate max-w-[150px] font-sans font-semibold" x-text="std.name"></span>
                                                 <input type="hidden" name="student_ids[]" :value="std.id">
                                             </div>
                                         </template>
                                         <template x-if="selectedStudents.length === 0">
                                             <div class="text-gray-400 italic py-2 text-center font-sans">No students selected.</div>
                                         </template>
                                     </div>
                                 </div>
     
                                 <button type="submit" 
                                         :disabled="selectedStudents.length === 0"
                                         :class="selectedStudents.length === 0 ? 'bg-gray-300 cursor-not-allowed opacity-60 text-gray-505' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs'"
                                         class="w-full mt-5 font-bold text-xs rounded-xl py-3 transition">
                                     Apply Bulk Assignment
                                 </button>
                             </div>
                         </form>
                     </div>
                 </div>

                <!-- Right: Split lists -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-white px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
                                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Unassigned Students</h3>
                            </div>
                            <span class="px-2.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-100 font-bold text-[10px] rounded-full">{{ count($unassignedStudents ?? []) }}</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/70 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                    <tr>
                                        <th class="w-12 px-4 py-3 text-center">Select</th>
                                        <th class="px-6 py-3 text-left">Enrollment</th>
                                        <th class="px-6 py-3 text-left">Name</th>
                                        <th class="px-6 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white text-xs">
                                    @forelse($unassignedStudents ?? [] as $stud)
                                        <tr class="hover:bg-indigo-50/10 transition-colors">
                                            <td class="text-center py-3">
                                                <input type="checkbox" class="student-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" value="{{ $stud->id }}" @change="$dispatch('student-selection-changed')">
                                            </td>
                                            <td class="px-6 py-3 font-bold text-indigo-950 font-mono tracking-wide" id="std-enroll-{{ $stud->id }}">{{ $stud->enrollment_number }}</td>
                                            <td class="px-6 py-3 text-gray-900 font-bold" id="std-name-{{ $stud->id }}">{{ $stud->name }}</td>
                                            <td class="px-6 py-3 text-right">
                                                <button @click="openAssignGuide({{ json_encode($stud) }})" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-1 rounded-lg transition font-bold">Assign</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-10 text-gray-400 italic">All students have guides assigned</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-white px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Assigned Students</h3>
                            </div>
                            <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 font-bold text-[10px] rounded-full">{{ count($assignedStudents ?? []) }}</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/70 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3 text-left">Enrollment</th>
                                        <th class="px-6 py-3 text-left">Name</th>
                                        <th class="px-6 py-3 text-left">Assigned Guide</th>
                                        <th class="px-6 py-3 text-left">Lock Status</th>
                                        <th class="px-6 py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white text-xs">
                                    @forelse($assignedStudents ?? [] as $stud)
                                        <tr class="hover:bg-indigo-50/10 transition-colors">
                                            <td class="px-6 py-3.5 font-bold text-indigo-950 font-mono tracking-wide">{{ $stud->enrollment_number }}</td>
                                            <td class="px-6 py-3.5 text-gray-900 font-bold">{{ $stud->name }}</td>
                                            <td class="px-6 py-3.5 text-gray-800 font-bold">
                                                <div class="flex items-center space-x-1.5 font-semibold text-gray-850">
                                                    <i class="fas fa-user-tie text-gray-400 text-[10px]"></i>
                                                    <span>{{ $stud->guide?->name }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3.5">
                                                @if($stud->is_locked)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase bg-indigo-50 text-indigo-700 border border-indigo-150 gap-1"><i class="fas fa-lock text-[8px]"></i> Locked</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase bg-gray-50 text-gray-400 border border-gray-150 gap-1"><i class="fas fa-lock-open text-[8px]"></i> Open</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3.5 text-right font-bold">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button @click="openAssignGuide({{ json_encode($stud) }})" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition text-[11px]">Change</button>
                                                    <form action="{{ route('admin.guide-assignments.release', $stud->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="bg-rose-50 text-rose-700 hover:bg-rose-100 px-2.5 py-1 rounded-lg transition text-[11px]">Release</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-10 text-gray-400 italic">No assigned guide records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 5: BATCH DIRECTORY -->
        <div x-show="activeTab === 'batches'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-white p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-layer-group text-sm"></i></span>
                            <h3 class="text-lg font-bold text-gray-900">Batch Directory</h3>
                        </div>
                        <p class="text-xs text-gray-455 font-semibold mt-1.5">Manage academic batches, batch guide assignments, and batch transfers.</p>
                    </div>
                    <div>
                        <button @click="openCreateBatch()" class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition shadow-xs">
                            <i class="fas fa-plus"></i>
                            <span>Create New Batch</span>
                        </button>
                    </div>
                </div>

                <div class="p-6 bg-gray-50/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($batches ?? [] as $batch)
                            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
                                <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50/20 rounded-full translate-x-8 -translate-y-8 flex items-center justify-center">
                                    <i class="fas fa-layer-group text-3xl text-indigo-100/40"></i>
                                </div>

                                <div class="relative">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.batches.show', $batch->id) }}" class="text-base font-black text-gray-950 hover:text-indigo-650 transition block leading-tight">{{ $batch->name }}</a>
                                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mt-0.5 block">Academic Batch</span>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3 mt-4">
                                        <!-- Default Guide -->
                                        <div class="flex items-center space-x-2.5 p-2.5 bg-gray-50 border border-gray-100 rounded-xl">
                                            <div class="bg-indigo-50/80 text-indigo-600 w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-xs">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <span class="block text-[8px] text-gray-400 font-bold uppercase tracking-wider">Default Guide</span>
                                                <span class="text-[11px] font-bold text-gray-800 truncate block">
                                                    {{ $batch->guide?->name ?? 'None' }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Total Students -->
                                        <div class="flex items-center space-x-2.5 p-2.5 bg-gray-50 border border-gray-100 rounded-xl">
                                            <div class="bg-blue-50/80 text-blue-600 w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-xs">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <span class="block text-[8px] text-gray-400 font-bold uppercase tracking-wider">Students</span>
                                                <span class="text-[11px] font-black text-gray-900 block">
                                                    {{ $batch->students_count }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Approved Applications -->
                                        <div class="flex items-center space-x-2.5 p-2.5 bg-gray-50 border border-gray-100 rounded-xl">
                                            <div class="bg-emerald-50/80 text-emerald-600 w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-xs">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <span class="block text-[8px] text-gray-400 font-bold uppercase tracking-wider">Approved Apps</span>
                                                <span class="text-[11px] font-bold text-emerald-700 block">
                                                    {{ $batch->approved_apps ?? 0 }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- NOC Certificates -->
                                        <div class="flex items-center space-x-2.5 p-2.5 bg-gray-50 border border-gray-100 rounded-xl">
                                            <div class="bg-pink-50/80 text-pink-600 w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-xs">
                                                <i class="fas fa-certificate"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <span class="block text-[8px] text-gray-400 font-bold uppercase tracking-wider">NOC Generated</span>
                                                <span class="text-[11px] font-bold text-pink-700 block">
                                                    {{ $batch->noc_count ?? 0 }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-2 relative z-10">
                                    <a href="{{ route('admin.batches.show', $batch->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs px-3.5 py-2 rounded-xl transition font-bold text-xs text-center flex-1">Manage</a>
                                    <button @click="openEditBatch({{ json_encode($batch) }})" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 px-3.5 py-2 rounded-xl transition font-bold text-xs flex-1">Edit</button>
                                    <form action="{{ route('admin.batches.destroy', $batch->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this batch?')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100/50 w-9 h-9 flex items-center justify-center rounded-xl transition font-bold text-xs" title="Delete Batch">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 6: AUTHORITY MANAGEMENT -->
        <div x-show="activeTab === 'authority_management'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-white p-6 border-b border-gray-100">
                    <div class="flex items-center space-x-2">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-users-cog text-sm"></i></span>
                        <h3 class="text-lg font-bold text-gray-900">Authority & Permissions Management</h3>
                    </div>
                    <p class="text-xs text-gray-455 font-semibold mt-1.5">Assign permissions (Guide, Approval Faculty, NOC Authority) directly to faculty users.</p>
                </div>

                <div class="p-6 bg-gray-50/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($faculty ?? [] as $fac)
                            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs hover:border-indigo-200 hover:shadow-sm transition-all duration-200 flex flex-col justify-between">
                                <form action="{{ route('admin.authority-management.update', $fac->id) }}" method="POST" class="h-full flex flex-col justify-between space-y-4">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h4 class="font-black text-gray-900 text-sm">{{ $fac->name }}</h4>
                                                <span class="block text-[10px] text-gray-400 font-semibold font-mono mt-0.5">{{ $fac->email }}</span>
                                            </div>
                                            <span class="p-2 bg-slate-50 text-slate-500 rounded-xl"><i class="fas fa-user-shield text-xs"></i></span>
                                        </div>
                                        
                                        <div class="mt-4 space-y-2.5 border-t border-gray-100 pt-3.5">
                                            <label class="flex items-center justify-between p-2 rounded-xl bg-gray-50/50 hover:bg-gray-100/50 border border-gray-100 transition cursor-pointer">
                                                <span class="text-xs font-semibold text-gray-700">Guide Role</span>
                                                <input type="checkbox" name="permissions[]" value="guide" {{ $fac->hasPermission('guide') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                            </label>
                                            <label class="flex items-center justify-between p-2 rounded-xl bg-gray-50/50 hover:bg-gray-100/50 border border-gray-100 transition cursor-pointer">
                                                <span class="text-xs font-semibold text-gray-700">Approval Faculty</span>
                                                <input type="checkbox" name="permissions[]" value="approval_faculty" {{ $fac->hasPermission('approval_faculty') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                            </label>
                                            <label class="flex items-center justify-between p-2 rounded-xl bg-gray-50/50 hover:bg-gray-100/50 border border-gray-100 transition cursor-pointer">
                                                <span class="text-xs font-semibold text-gray-700">NOC Authority</span>
                                                <input type="checkbox" name="permissions[]" value="noc_authority" {{ $fac->hasPermission('noc_authority') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-xs">
                                        Update Permissions
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 7: AUDIT LOGS -->
        <div x-show="activeTab === 'audit_logs'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-white p-6 border-b border-gray-100">
                    <div class="flex items-center space-x-2">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-history text-sm"></i></span>
                        <h3 class="text-lg font-bold text-gray-900">Audit Logs Registry</h3>
                    </div>
                    <p class="text-xs text-gray-450 font-semibold mt-1.5">Monitor all system administration events and modifications in real time.</p>
                </div>

                <!-- Audit log filters -->
                <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="bg-gray-50/50 p-5 border-b border-gray-150 grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                    <select name="action_type" class="w-full bg-white border border-gray-255 rounded-xl py-2.5 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500">
                        <option value="">All Action Types</option>
                        @foreach($actionTypes ?? [] as $type)
                            <option value="{{ $type }}" {{ request('action_type') === $type ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>

                    <div class="flex space-x-2">
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-white border border-gray-255 rounded-xl py-2.5 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500">
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-white border border-gray-255 rounded-xl py-2.5 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500">
                    </div>

                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search details..." class="w-full bg-white border border-gray-255 rounded-xl pl-9 pr-4 py-2.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500">
                        <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400 text-xs"></i>
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl py-2.5 transition shadow-xs">Filter</button>
                        <a href="{{ route('admin.audit-logs.index') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs rounded-xl py-2.5 transition flex items-center justify-center">Clear</a>
                    </div>
                </form>

                <div class="p-6 bg-gray-50/30 space-y-4">
                    @forelse($auditLogs ?? [] as $log)
                        @php
                            $actionLower = strtolower($log->action);
                            $iconClass = 'fa-info-circle bg-blue-50 text-blue-600 border-blue-100';
                            if (str_contains($actionLower, 'create') || str_contains($actionLower, 'add') || str_contains($actionLower, 'import')) {
                                $iconClass = 'fa-plus bg-emerald-50 text-emerald-600 border-emerald-100';
                            } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'remove')) {
                                $iconClass = 'fa-trash-alt bg-rose-50 text-rose-600 border-rose-100';
                            } elseif (str_contains($actionLower, 'deactivate') || str_contains($actionLower, 'activate') || str_contains($actionLower, 'toggle')) {
                                $iconClass = 'fa-power-off bg-amber-50 text-amber-600 border-amber-100';
                            } elseif (str_contains($actionLower, 'assign') || str_contains($actionLower, 'move') || str_contains($actionLower, 'update')) {
                                $iconClass = 'fa-cog bg-indigo-50 text-indigo-600 border-indigo-100';
                            }
                        @endphp
                        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs flex items-start space-x-4 hover:border-indigo-200 hover:shadow-xs transition duration-200">
                            <div class="p-3 rounded-xl flex-shrink-0 w-11 h-11 flex items-center justify-center border {{ $iconClass }}">
                                <i class="fas text-sm"></i>
                            </div>
                            <div class="flex-grow space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-xs text-indigo-950">{{ $log->admin_name }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase bg-gray-100 text-gray-500">{{ $log->action_type ?: 'General' }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider font-mono">{{ $log->timestamp->format('M d, Y h:i A') }}</span>
                                </div>
                                <p class="text-xs text-gray-750 font-semibold leading-relaxed">{{ $log->action }}</p>
                                @if($log->target)
                                    <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200 font-mono text-[9px] text-gray-550 break-all">
                                        <span class="block text-[8px] text-gray-400 font-bold uppercase tracking-wider mb-1 font-sans">Target Details</span>
                                        {{ $log->target }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white text-center py-12 text-gray-450 border border-gray-200 rounded-2xl shadow-xs italic font-semibold text-xs">
                            <i class="fas fa-history text-lg mb-2 block text-gray-300"></i>
                            No audit log records matching criteria.
                        </div>
                    @endforelse

                    @if(isset($auditLogs) && method_exists($auditLogs, 'links'))
                        <div class="pt-4">
                            {{ $auditLogs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <!-- TAB 10: SYSTEM DIAGNOSTICS -->
        <div x-show="activeTab === 'system_diagnostics'" x-transition class="space-y-6">
            <div class="space-y-6" 
                 x-data="{ diagnostics: null, loading: false, activeSubTab: 'modules' }"
                 x-init="
                    $watch('activeTab', value => {
                        if (value === 'system_diagnostics' && !diagnostics) {
                            loading = true;
                            fetch('{{ route('admin.system-diagnostics') }}')
                                .then(r => r.json())
                                .then(d => { diagnostics = d; loading = false; })
                                .catch(err => { console.error(err); loading = false; });
                        }
                    });
                    if (activeTab === 'system_diagnostics' && !diagnostics) {
                        loading = true;
                        fetch('{{ route('admin.system-diagnostics') }}')
                            .then(r => r.json())
                            .then(d => { diagnostics = d; loading = false; })
                            .catch(err => { console.error(err); loading = false; });
                    }
                 ">
                
                <div x-show="loading" class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500 shadow-sm">
                    <i class="fas fa-circle-notch fa-spin text-3xl text-indigo-600 mb-3"></i>
                    <p class="text-sm font-semibold">Running comprehensive system health scans...</p>
                </div>

                <div x-show="!loading && diagnostics" class="space-y-6" style="display: none;">
                    
                    <!-- Top stats section -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Health Score Card -->
                        <div class="bg-white rounded-2xl border border-gray-250 p-6 shadow-sm flex flex-col items-center justify-center text-center relative overflow-hidden">
                            <div class="absolute -right-10 -bottom-10 opacity-5 text-indigo-600">
                                <i class="fas fa-heartbeat text-9xl"></i>
                            </div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Overall System Health</h4>
                            
                            <!-- Circular Gauge -->
                            <div class="relative flex items-center justify-center w-36 h-36">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                    <!-- Background Circle -->
                                    <circle class="text-gray-100" stroke-width="8" stroke="currentColor" fill="none" r="38" cx="50" cy="50"></circle>
                                    <!-- Foreground Progress Circle -->
                                    <circle class="text-indigo-600 transition-all duration-1000 ease-out" 
                                            stroke-width="8" 
                                            :stroke-dasharray="2 * Math.PI * 38" 
                                            :stroke-dashoffset="((100 - diagnostics.score) / 100) * (2 * Math.PI * 38)" 
                                            stroke-linecap="round" 
                                            stroke="currentColor" 
                                            fill="none" 
                                            r="38" cx="50" cy="50"></circle>
                                </svg>
                                <div class="absolute text-center">
                                    <span class="text-3xl font-black text-gray-900" x-text="diagnostics.score + '%'"></span>
                                    <span class="block text-[9px] font-bold text-emerald-600 uppercase tracking-wider mt-0.5" x-text="diagnostics.score >= 90 ? 'EXCELLENT' : (diagnostics.score >= 70 ? 'STABLE' : 'ACTION REQUIRED')"></span>
                                </div>
                            </div>
                            
                            <p class="text-xs text-gray-500 mt-4 leading-relaxed">
                                System is fully operational. <span class="font-bold text-indigo-600" x-text="diagnostics.score + '%'"></span> of integrity checks are currently green.
                            </p>
                        </div>

                        <!-- System Specs Card -->
                        <div class="bg-white rounded-2xl border border-gray-250 p-6 shadow-sm col-span-1 lg:col-span-2 space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">System Specifications</h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl">
                                    <div class="bg-indigo-50 text-indigo-600 p-2 rounded-lg text-sm w-9 h-9 flex items-center justify-center flex-shrink-0">
                                        <i class="fab fa-laravel text-lg"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-400 font-bold uppercase">Laravel Version</span>
                                        <span class="text-xs font-bold text-gray-800" x-text="diagnostics.system_info?.laravel_version || 'N/A'"></span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl">
                                    <div class="bg-indigo-50 text-indigo-600 p-2 rounded-lg text-sm w-9 h-9 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-database text-lg"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-400 font-bold uppercase">Database Driver</span>
                                        <span class="text-xs font-bold text-gray-800 uppercase" x-text="diagnostics.system_info?.db_connection || 'N/A'"></span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl">
                                    <div class="bg-indigo-50 text-indigo-600 p-2 rounded-lg text-sm w-9 h-9 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-memory text-lg"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-400 font-bold uppercase">Memory Consumption</span>
                                        <span class="text-xs font-bold text-gray-800" x-text="diagnostics.system_info?.memory_usage || 'N/A'"></span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl">
                                    <div class="bg-indigo-50 text-indigo-600 p-2 rounded-lg text-sm w-9 h-9 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-code text-lg"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-400 font-bold uppercase">PHP Engine Version</span>
                                        <span class="text-xs font-bold text-gray-800" x-text="diagnostics.environment?.php_version?.detail || 'N/A'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- INI Path indicator -->
                            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500 font-mono">
                                <span class="font-sans font-bold text-gray-400">INI Loaded Config Path:</span>
                                <span class="text-right break-all max-w-[70%]" x-text="diagnostics.environment?.php_ini_path?.detail || 'Unknown'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Inner Navigation Tabs -->
                    <div class="flex border-b border-gray-250 gap-2 bg-gray-100/60 p-1.5 rounded-xl">
                        <button @click="activeSubTab = 'modules'" 
                                :class="activeSubTab === 'modules' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'" 
                                class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                            <i class="fas fa-cubes text-sm"></i>
                            <span>Core ERP Modules</span>
                        </button>
                        <button @click="activeSubTab = 'environment'" 
                                :class="activeSubTab === 'environment' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'" 
                                class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                            <i class="fas fa-server text-sm"></i>
                            <span>PHP & Library Specs</span>
                        </button>
                    </div>

                    <!-- SUBTAB 1: Core ERP Modules Health Grid -->
                    <div x-show="activeSubTab === 'modules'" class="grid grid-cols-1 md:grid-cols-2 gap-4" x-transition>
                        <template x-for="(module, key) in diagnostics.modules" :key="key">
                            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs flex items-start space-x-4 hover:border-indigo-200 hover:shadow-sm transition-all duration-200">
                                <div class="p-3 rounded-xl flex-shrink-0 w-11 h-11 flex items-center justify-center"
                                     :class="module.status ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                    <i class="fas" :class="module.icon || 'fa-info-circle'"></i>
                                </div>
                                <div class="flex-grow space-y-1">
                                    <div class="flex justify-between items-center">
                                        <h5 class="text-xs font-bold text-gray-800" x-text="module.label"></h5>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider"
                                              :class="module.status ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                              x-text="module.status ? 'ONLINE' : 'ATTENTION'"></span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 leading-relaxed font-medium" x-text="module.detail"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- SUBTAB 2: PHP & Environment Specs Sheet -->
                    <div x-show="activeSubTab === 'environment'" class="bg-white rounded-2xl border border-gray-250 overflow-hidden" x-transition>
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Configuration/Extension Check</th>
                                    <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Details / Config Value</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 font-mono">
                                <template x-for="(check, key) in diagnostics.environment" :key="key">
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 font-sans font-semibold text-gray-800" x-text="check.label || key.toUpperCase().replace(/_/g, ' ')"></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[9px] tracking-wider" 
                                                  :class="check.status ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'" 
                                                  x-text="check.status ? 'OK' : 'FAIL'"></span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 font-mono" x-text="check.detail"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- MODALS AND DIALOGS -->
    <!-- ============================================== -->

    <!-- 1. BATCH MODAL (Create/Edit) -->
    <div x-show="showBatchModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showBatchModal = false" class="fixed inset-0 transition-opacity bg-black/40 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-150" x-transition>
                <div class="bg-white px-6 py-5 border-b border-gray-150 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl"><i class="fas fa-layer-group text-xs"></i></span>
                        <span x-text="modalMode === 'create' ? 'Create New Batch' : 'Edit Batch Name'"></span>
                    </h3>
                    <button @click="showBatchModal = false" class="text-gray-400 hover:text-gray-655 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="modalMode === 'create' ? '{{ route('admin.batches.store') }}' : '{{ url('admin/batches') }}/' + batchForm.id" method="POST" class="p-6 space-y-5">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
 
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Batch Code / Name</label>
                        <input type="text" name="name" x-model="batchForm.name" required placeholder="e.g. 2026-IT" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                    </div>
 
                    <div class="pt-4 border-t border-gray-150 flex justify-end space-x-3">
                        <button type="button" @click="showBatchModal = false" class="px-4 py-2.5 border border-gray-250 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <!-- 2. STUDENT MODAL (Create/Edit) -->
    <div x-show="showStudentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showStudentModal = false" class="fixed inset-0 transition-opacity bg-black/40 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150" x-transition>
                <div class="bg-white px-6 py-5 border-b border-gray-150 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl"><i class="fas fa-user-graduate text-xs"></i></span>
                        <span x-text="modalMode === 'create' ? 'Add Student Record' : 'Edit Student Record'"></span>
                    </h3>
                    <button @click="showStudentModal = false" class="text-gray-400 hover:text-gray-655 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="modalMode === 'create' ? '{{ route('admin.student-directory.store') }}' : '{{ url('admin/student-directory') }}/' + studentForm.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
 
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Enrollment Number</label>
                            <input type="text" name="enrollment_number" x-model="studentForm.enrollment_number" required placeholder="e.g. 21IT001" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Student Name</label>
                            <input type="text" name="name" x-model="studentForm.name" required placeholder="e.g. Jane Doe" class="w-full border border-gray-255 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                        </div>
                    </div>
 
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                            <input type="email" name="email" x-model="studentForm.email" required placeholder="student@edu.in" class="w-full border border-gray-255 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Department</label>
                            <input type="text" name="department" x-model="studentForm.department" required placeholder="e.g. Information Technology" class="w-full border border-gray-255 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                        </div>
                    </div>
 
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Semester</label>
                            <input type="number" name="semester" x-model="studentForm.semester" required min="1" max="10" class="w-full border border-gray-255 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Academic Batch</label>
                            <select name="batch_id" x-model="studentForm.batch_id" class="w-full border border-gray-255 bg-white rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                                <option value="">-- Choose Batch (Optional) --</option>
                                @foreach($batches ?? [] as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
 
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assigned Guide / Faculty</label>
                        <select name="guide_id" x-model="studentForm.guide_id" class="w-full border border-gray-255 bg-white rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                            <option value="">-- Unassigned --</option>
                            @foreach($guides ?? [] as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }} ({{ $fac->department }})</option>
                            @endforeach
                        </select>
                    </div>
 
                    <div class="pt-4 border-t border-gray-150 flex justify-end space-x-3">
                        <button type="button" @click="showStudentModal = false" class="px-4 py-2.5 border border-gray-250 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs" x-text="modalMode === 'create' ? 'Save' : 'Update'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <!-- 3. FACULTY MODAL (Create/Edit) -->
    <div x-show="showFacultyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showFacultyModal = false" class="fixed inset-0 transition-opacity bg-black/40 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150" x-transition>
                <div class="bg-white px-6 py-5 border-b border-gray-150 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl"><i class="fas fa-chalkboard-teacher text-xs"></i></span>
                        <span x-text="modalMode === 'create' ? 'Add Faculty Record' : 'Edit Faculty Record'"></span>
                    </h3>
                    <button @click="showFacultyModal = false" class="text-gray-400 hover:text-gray-655 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="modalMode === 'create' ? '{{ route('admin.faculty-directory.store') }}' : '{{ url('admin/faculty-directory') }}/' + facultyForm.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
 
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Faculty ID</label>
                            <input type="text" name="faculty_id" x-model="facultyForm.faculty_id" required placeholder="e.g. F01" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Faculty Name</label>
                            <input type="text" name="name" x-model="facultyForm.name" required placeholder="e.g. Dr. John Doe" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                        </div>
                    </div>
 
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                            <input type="email" name="email" x-model="facultyForm.email" required placeholder="faculty@ac.in" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Department</label>
                            <input type="text" name="department" x-model="facultyForm.department" required placeholder="e.g. IT" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                        </div>
                    </div>
 
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Designation</label>
                        <input type="text" name="designation" x-model="facultyForm.designation" required placeholder="e.g. Assistant Professor" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                    </div>
 
                    <div class="pt-4 border-t border-gray-150 flex justify-end space-x-3">
                        <button type="button" @click="showFacultyModal = false" class="px-4 py-2.5 border border-gray-250 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs" x-text="modalMode === 'create' ? 'Save' : 'Update'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 

 
    <!-- 5. MOVE BATCH MODAL -->
    <div x-show="showMoveBatchModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showMoveBatchModal = false" class="fixed inset-0 transition-opacity bg-black/40 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-gray-150" x-transition>
                <div class="bg-white px-6 py-5 border-b border-gray-150 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl"><i class="fas fa-layer-group text-xs"></i></span>
                        <span>Move Batch: <span class="text-indigo-600" x-text="moveBatchForm.student_name"></span></span>
                    </h3>
                    <button @click="showMoveBatchModal = false" class="text-gray-400 hover:text-gray-655 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="'{{ url('admin/student-directory') }}/' + moveBatchForm.student_id + '/move-batch'" method="POST" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Batch</label>
                        <select name="batch_id" x-model="moveBatchForm.batch_id" required class="w-full border border-gray-250 bg-white rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                            <option value="">-- Choose Batch --</option>
                            @foreach($batches ?? [] as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
 
                    <div class="pt-4 border-t border-gray-150 flex justify-end space-x-3">
                        <button type="button" @click="showMoveBatchModal = false" class="px-4 py-2.5 border border-gray-250 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <!-- 6. ASSIGN GUIDE MODAL -->
    <div x-show="showAssignGuideModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showAssignGuideModal = false" class="fixed inset-0 transition-opacity bg-black/40 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-gray-150" x-transition>
                <div class="bg-white px-6 py-5 border-b border-gray-150 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl"><i class="fas fa-user-check text-xs"></i></span>
                        <span>Assign Guide: <span class="text-indigo-600" x-text="assignGuideForm.student_name"></span></span>
                    </h3>
                    <button @click="showAssignGuideModal = false" class="text-gray-400 hover:text-gray-655 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="'{{ url('admin/student-directory') }}/' + assignGuideForm.student_id + '/assign-guide'" method="POST" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Faculty Guide</label>
                        <select name="guide_id" x-model="assignGuideForm.guide_id" required class="w-full border border-gray-250 bg-white rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition duration-150">
                            <option value="">-- Choose Guide --</option>
                            @foreach($guides ?? [] as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }} ({{ $fac->department }})</option>
                            @endforeach
                        </select>
                    </div>
 
                    <div class="pt-4 border-t border-gray-150 flex justify-end space-x-3">
                        <button type="button" @click="showAssignGuideModal = false" class="px-4 py-2.5 border border-gray-250 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-xs">Assign & Lock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <!-- 7. STUDENT DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteStudentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showDeleteStudentModal = false" class="fixed inset-0 transition-opacity bg-black/40 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-155 shadow-rose-100" x-transition>
                <div class="bg-white px-6 py-5 border-b border-gray-150 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs animate-pulse"><i class="fas fa-exclamation-triangle"></i></span>
                        <h3 class="text-xs font-black uppercase tracking-wider text-rose-700">Warning: Student Deletion</h3>
                    </div>
                    <button type="button" @click="showDeleteStudentModal = false" class="text-gray-400 hover:text-gray-655 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
 
                <form :action="'{{ url('admin/student-directory') }}/' + deleteStudentData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('DELETE')
 
                    <p class="text-xs font-semibold text-gray-600 leading-relaxed">You are about to permanently delete this student record. Confirming this action will erase the record from the database.</p>
 
                    <div class="bg-red-50/50 border border-red-100 rounded-xl p-4 text-xs space-y-3 text-red-955 font-medium">
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[8px]">Student Name</span>
                            <span class="font-bold text-sm text-gray-900" x-text="deleteStudentData.name"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[8px]">Enrollment Number</span>
                            <span class="font-bold font-mono text-sm text-gray-900 tracking-wide" x-text="deleteStudentData.enrollment_number"></span>
                        </div>
                    </div>
 
                    <p class="text-[10px] text-red-600 font-bold flex items-center uppercase tracking-wider">
                        <i class="fas fa-info-circle mr-1 text-red-600 text-xs"></i>This action cannot be undone.
                    </p>
 
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">To confirm deletion, type the Enrollment Number exactly as shown:</label>
                        <input type="text" name="confirmation_text" x-model="deleteStudentConfirmationInput" required placeholder="Type Enrollment Number" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-red-100 focus:border-red-500 transition duration-150 font-mono tracking-wide">
                    </div>
 
                    <div class="pt-4 border-t border-gray-150 flex justify-end space-x-3">
                        <button type="button" @click="showDeleteStudentModal = false" class="px-4 py-2.5 border border-gray-250 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" :disabled="deleteStudentConfirmationInput !== deleteStudentData.enrollment_number" :class="deleteStudentConfirmationInput !== deleteStudentData.enrollment_number ? 'opacity-50 cursor-not-allowed bg-red-400' : 'bg-red-600 hover:bg-red-700 text-white font-bold shadow-xs'" class="px-5 py-2.5 rounded-xl text-xs font-bold transition">Delete Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <!-- 8. FACULTY DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteFacultyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showDeleteFacultyModal = false" class="fixed inset-0 transition-opacity bg-black/40 backdrop-blur-xs"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-155 shadow-rose-100" x-transition>
                <div class="bg-white px-6 py-5 border-b border-gray-150 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs animate-pulse"><i class="fas fa-exclamation-triangle"></i></span>
                        <h3 class="text-xs font-black uppercase tracking-wider text-rose-700">Warning: Faculty Deletion</h3>
                    </div>
                    <button type="button" @click="showDeleteFacultyModal = false" class="text-gray-400 hover:text-gray-655 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
 
                <form :action="'{{ url('admin/faculty-directory') }}/' + deleteFacultyData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('DELETE')
 
                    <p class="text-xs font-semibold text-gray-600 leading-relaxed">You are about to permanently delete this faculty record. Confirming this action will erase the record from the database.</p>
 
                    <div class="bg-red-50/50 border border-red-100 rounded-xl p-4 text-xs space-y-3 text-red-955 font-medium">
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[8px]">Faculty Name</span>
                            <span class="font-bold text-sm text-gray-900" x-text="deleteFacultyData.name"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-400 uppercase tracking-wider block text-[8px]">Faculty Email</span>
                            <span class="font-bold text-sm text-gray-900 font-mono" x-text="deleteFacultyData.email"></span>
                        </div>
                    </div>
 
                    <p class="text-[10px] text-red-600 font-bold flex items-center uppercase tracking-wider">
                        <i class="fas fa-info-circle mr-1 text-red-600 text-xs"></i>This action cannot be undone.
                    </p>
 
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">To confirm deletion, type the faculty full name exactly as shown:</label>
                        <input type="text" name="confirmation_text" x-model="deleteFacultyConfirmationInput" required placeholder="Type Faculty Full Name" class="w-full border border-gray-250 rounded-xl py-2.5 px-3.5 text-xs outline-none focus:ring-2 focus:ring-red-100 focus:border-red-500 transition duration-150">
                    </div>
 
                    <div class="pt-4 border-t border-gray-150 flex justify-end space-x-3">
                        <button type="button" @click="showDeleteFacultyModal = false" class="px-4 py-2.5 border border-gray-250 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" :disabled="deleteFacultyConfirmationInput !== deleteFacultyData.name" :class="deleteFacultyConfirmationInput !== deleteFacultyData.name ? 'opacity-50 cursor-not-allowed bg-red-400' : 'bg-red-600 hover:bg-red-700 text-white font-bold shadow-xs'" class="px-5 py-2.5 rounded-xl text-xs font-bold transition">Delete Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



</div>
@endsection
