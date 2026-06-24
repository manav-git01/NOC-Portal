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
    showImportModal: false,
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
    importForm: { type: 'student', file: '' },
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
    openImport(type) {
        this.importForm.type = type;
        this.showImportModal = true;
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

        <!-- Excel Import Report Banner -->
        @if(session('import_report'))
            <div class="mb-8 bg-white border border-indigo-100 rounded-xl p-6 shadow-md border-t-4 border-t-indigo-600">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                            <i class="fas fa-file-import text-xl animate-bounce"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ session('import_report')['type'] }} Import Summary</h4>
                            <p class="text-sm text-gray-500">Processed uploaded Excel records</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 transition" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
                        <p class="text-emerald-600 font-medium text-sm">Successfully Created</p>
                        <h4 class="text-3xl font-black text-emerald-700 mt-1">{{ session('import_report')['success'] }}</h4>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                        <p class="text-amber-600 font-medium text-sm">Skipped Duplicates</p>
                        <h4 class="text-3xl font-black text-amber-700 mt-1">{{ session('import_report')['duplicates'] }}</h4>
                    </div>
                    <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 text-center">
                        <p class="text-rose-600 font-medium text-sm">Warnings / Errors</p>
                        <h4 class="text-3xl font-black text-rose-700 mt-1">{{ count(session('import_report')['errors']) }}</h4>
                    </div>
                </div>
                @if(count(session('import_report')['errors']) > 0)
                    <div class="mt-4">
                        <p class="text-sm font-semibold text-gray-800 mb-2">Import Log Details:</p>
                        <div class="bg-gray-50 rounded-lg p-3 max-h-32 overflow-y-auto border border-gray-200 text-xs text-rose-600 space-y-1 font-mono">
                            @foreach(session('import_report')['errors'] as $error)
                                <p>&bull; {{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- General Stats cards visible contextually -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sky-100 text-xs font-semibold uppercase tracking-wider">Students</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalStudents ?? 0 }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-teal-400 to-teal-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-teal-100 text-xs font-semibold uppercase tracking-wider">Faculty</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalFaculty ?? 0 }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wider">Guides</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalGuides ?? 0 }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-user-tie text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-rose-400 to-rose-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-rose-100 text-xs font-semibold uppercase tracking-wider">NOCs Generated</p>
                        <h3 class="text-3xl font-black mt-2">{{ $generatedNocs ?? 0 }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-certificate text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (10 modules ERP navigation) -->
        <div class="flex flex-wrap border-b border-gray-200 gap-2 mb-8 bg-white p-2 rounded-xl shadow-sm">
            <a href="{{ route('admin.dashboard', ['tab' => 'statistics']) }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ ($activeTab === 'statistics' || $activeTab === 'dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-chart-line text-sm"></i>
                <span>Statistics</span>
            </a>

            <a href="{{ route('admin.student-directory.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'student_directory' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-user-graduate text-sm"></i>
                <span>Student Directory</span>
            </a>

            <a href="{{ route('admin.faculty-directory.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'faculty_directory' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-chalkboard-teacher text-sm"></i>
                <span>Faculty Directory</span>
            </a>

            <a href="{{ route('admin.guide-assignments.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'guide_assignments' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-user-check text-sm"></i>
                <span>Guide Assignments</span>
            </a>

            <a href="{{ route('admin.dashboard', ['tab' => 'batches']) }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'batches' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-layer-group text-sm"></i>
                <span>Batch Directory</span>
            </a>

            <a href="{{ route('admin.authority-management.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'authority_management' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-users-cog text-sm"></i>
                <span>Authority Management</span>
            </a>

            <a href="{{ route('admin.account-management.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'account_management' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-user-shield text-sm"></i>
                <span>Account Management</span>
            </a>

            <a href="{{ route('admin.audit-logs.index') }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'audit_logs' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-history text-sm"></i>
                <span>Audit Logs</span>
            </a>

            <a href="{{ route('admin.dashboard', ['tab' => 'system_diagnostics']) }}"
               class="flex items-center space-x-2 px-4 py-2.5 rounded-lg text-xs font-bold transition duration-150 {{ $activeTab === 'system_diagnostics' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fas fa-diagnoses text-sm"></i>
                <span>Diagnostics</span>
            </a>
        </div>


        <!-- ============================================== -->
        <!-- TAB CONTENTS -->
        <!-- ============================================== -->

        <!-- TAB 1: STATISTICS / LANDING -->
        <div x-show="activeTab === 'statistics' || activeTab === 'dashboard'" x-transition class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Centralized ERP Dashboard</h3>
                <p class="text-sm text-gray-500 mb-6">Welcome to the NOC Portal Control Center. Manage directories, assign guides, monitor accounts and track system changes.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <h4 class="font-bold text-gray-800 text-sm mb-3">Application Pipeline</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Applications:</span>
                                <span class="font-bold text-gray-800">{{ $totalApplications ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Pending Review:</span>
                                <span class="font-bold text-yellow-600">{{ $pendingApplications ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Approved:</span>
                                <span class="font-bold text-emerald-600">{{ $approvedApplications ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <h4 class="font-bold text-gray-800 text-sm mb-3">User Distribution</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Students:</span>
                                <span class="font-bold text-gray-800">{{ $totalStudents ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Faculty:</span>
                                <span class="font-bold text-gray-800">{{ $totalFaculty ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Active Guides:</span>
                                <span class="font-bold text-indigo-600">{{ $totalGuides ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <h4 class="font-bold text-gray-800 text-sm mb-3">System Metrics</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Batches:</span>
                                <span class="font-bold text-gray-800">{{ $totalBatches ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">NOC Generated:</span>
                                <span class="font-bold text-rose-600">{{ $generatedNocs ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: STUDENT MASTER DIRECTORY -->
        <div x-show="activeTab === 'student_directory'" x-transition class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Student Master Directory</h3>
                        <p class="text-sm text-gray-500">Query and filter student details, academic batches, assigned guides and accounts.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="openImport('student')" class="flex items-center justify-center space-x-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-semibold text-xs transition shadow-sm">
                            <i class="fas fa-file-import text-indigo-600"></i>
                            <span>Import Master List</span>
                        </button>
                        <button @click="openCreateStudent()" class="flex items-center justify-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-xs transition shadow-sm">
                            <i class="fas fa-plus"></i>
                            <span>Add Student Manually</span>
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('admin.student-directory.index') }}" class="bg-gray-50 p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-5 gap-3">
                    <input type="hidden" name="tab" value="student_directory">
                    <div class="relative col-span-1 sm:col-span-2">
                        <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="Search by name, email, or enrollment..." class="w-full bg-white border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    </div>

                    <select name="batch_id" class="w-full bg-white border border-gray-300 rounded-lg py-2 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Batches</option>
                        @foreach($batches ?? [] as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>

                    <select name="guide_id" class="w-full bg-white border border-gray-300 rounded-lg py-2 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Guides</option>
                        @foreach($faculty ?? [] as $fac)
                            <option value="{{ $fac->id }}" {{ request('guide_id') == $fac->id ? 'selected' : '' }}>{{ $fac->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex space-x-2">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg py-2 px-3 transition shadow-sm">Filter</button>
                        <a href="{{ route('admin.student-directory.index') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs rounded-lg py-2 px-3 transition">Reset</a>
                    </div>
                </form>

                <!-- Students Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment Number</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department & Sem</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Guide</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Account Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($students ?? [] as $stud)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $stud->enrollment_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $stud->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $stud->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <span class="font-medium text-gray-700">{{ $stud->department ?? 'N/A' }}</span>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">Semester: {{ $stud->semester ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-600">{{ $stud->batch?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($stud->guide)
                                            <div class="flex items-center space-x-1.5">
                                                <span class="font-bold text-gray-800">{{ $stud->guide->name }}</span>
                                                @if($stud->is_locked)
                                                    <i class="fas fa-lock text-[10px] text-indigo-500" title="Locked Assignment"></i>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $stud->account_status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($stud->account_status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500') }}">
                                            {{ $stud->account_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold space-x-1.5">
                                        <button @click="openEditStudent({{ json_encode($stud) }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button @click="openMoveBatch({{ json_encode($stud) }})" class="text-teal-600 hover:text-teal-900">Move Batch</button>
                                        <button @click="openAssignGuide({{ json_encode($stud) }})" class="text-purple-600 hover:text-purple-900">Assign Guide</button>
                                        @if($stud->guide_id)
                                            <form action="{{ route('admin.student-directory.remove-guide', $stud->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-amber-600 hover:text-amber-900">Release Guide</button>
                                            </form>
                                        @endif
                                        <button @click="openDeleteStudent({{ json_encode($stud) }})" class="text-rose-600 hover:text-rose-900">Delete</button>
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Faculty Directory</h3>
                        <p class="text-sm text-gray-500">Query and manage faculty profiles and configurations.</p>
                    </div>
                    <div>
                        <button @click="openCreateFaculty()" class="flex items-center justify-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-xs transition shadow-sm">
                            <i class="fas fa-plus"></i>
                            <span>Add Faculty Manually</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Faculty ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Faculty Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Designation</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Assigned Students</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Account Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($faculty ?? [] as $fac)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $fac->faculty_id ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">
                                        {{ $fac->name }}
                                        <div class="text-xs text-gray-400 font-medium font-mono mt-0.5">{{ $fac->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $fac->department }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $fac->designation }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-800">{{ $fac->students_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $fac->account_status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $fac->account_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold space-x-1.5">
                                        <button @click="openEditFaculty({{ json_encode($fac) }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <form action="{{ route('admin.faculty-directory.deactivate', $fac->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-amber-600 hover:text-amber-900">
                                                {{ $fac->account_status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <button @click="openDeleteFaculty({{ json_encode($fac) }})" class="text-rose-600 hover:text-rose-900">Delete</button>
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Bulk Assign Guide</h3>
                    <p class="text-xs text-gray-500">Select students on the right and choose their new guide and batch configuration.</p>
                    
                    <form action="{{ route('admin.guide-assignments.bulk-assign') }}" method="POST" class="space-y-4">
                        @csrf
                        <div x-data="{ selectedStudents: [] }">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Choose Guide</label>
                            <select name="guide_id" required class="w-full border border-gray-300 rounded-lg py-2 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Select Guide --</option>
                                @foreach($guides ?? [] as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>

                            <label class="block text-xs font-bold text-gray-700 mb-1 mt-3">Choose Batch (Optional)</label>
                            <select name="batch_id" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Select Batch --</option>
                                @foreach($batches ?? [] as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>

                            <div class="mt-4 bg-gray-50 border border-gray-150 p-3 rounded-lg text-xs">
                                <span class="font-bold text-gray-700">Selected Students:</span>
                                <div class="mt-1 max-h-32 overflow-y-auto space-y-1 font-mono">
                                    <template x-for="stdId in Array.from(document.querySelectorAll('.student-checkbox:checked')).map(el => el.value)">
                                        <div class="flex justify-between py-0.5 border-b border-gray-100">
                                            <span class="text-indigo-600 font-bold" x-text="document.querySelector('#std-enroll-' + stdId).innerText"></span>
                                            <span class="text-gray-500" x-text="document.querySelector('#std-name-' + stdId).innerText"></span>
                                            <input type="hidden" name="student_ids[]" :value="stdId">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <button type="submit" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg py-2 px-3 transition shadow-sm">
                                Apply Bulk Assignment
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Split lists -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-white p-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Unassigned Students</h3>
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-600 font-bold text-[10px] rounded-full">{{ count($unassignedStudents ?? []) }}</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50 text-xs">
                                    <tr>
                                        <th class="w-10 px-4 py-2">Select</th>
                                        <th class="px-4 py-2 text-left">Enrollment</th>
                                        <th class="px-4 py-2 text-left">Name</th>
                                        <th class="px-4 py-2 text-left">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white text-xs">
                                    @forelse($unassignedStudents ?? [] as $stud)
                                        <tr>
                                            <td class="text-center py-2">
                                                <input type="checkbox" class="student-checkbox rounded text-indigo-600" value="{{ $stud->id }}" @change="$dispatch('student-selection-changed')">
                                            </td>
                                            <td class="px-4 py-2 font-bold text-gray-700" id="std-enroll-{{ $stud->id }}">{{ $stud->enrollment_number }}</td>
                                            <td class="px-4 py-2 text-gray-900 font-medium" id="std-name-{{ $stud->id }}">{{ $stud->name }}</td>
                                            <td class="px-4 py-2">
                                                <button @click="openAssignGuide({{ json_encode($stud) }})" class="text-indigo-600 font-bold hover:text-indigo-900">Assign</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-8 text-gray-400 italic">All students have guides assigned.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-white p-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Assigned Students</h3>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 font-bold text-[10px] rounded-full">{{ count($assignedStudents ?? []) }}</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50 text-xs">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Enrollment</th>
                                        <th class="px-4 py-2 text-left">Name</th>
                                        <th class="px-4 py-2 text-left">Assigned Guide</th>
                                        <th class="px-4 py-2 text-left">Lock Status</th>
                                        <th class="px-4 py-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white text-xs">
                                    @forelse($assignedStudents ?? [] as $stud)
                                        <tr class="hover:bg-gray-50/50 transition">
                                            <td class="px-4 py-2.5 font-bold text-indigo-900">{{ $stud->enrollment_number }}</td>
                                            <td class="px-4 py-2.5 text-gray-900 font-medium">{{ $stud->name }}</td>
                                            <td class="px-4 py-2.5 text-gray-700 font-bold">{{ $stud->guide?->name }}</td>
                                            <td class="px-4 py-2.5">
                                                @if($stud->is_locked)
                                                    <span class="inline-flex items-center text-indigo-600 font-bold gap-1"><i class="fas fa-lock"></i> Locked</span>
                                                @else
                                                    <span class="inline-flex items-center text-gray-400 gap-1"><i class="fas fa-lock-open"></i> Open</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-bold space-x-2">
                                                <button @click="openAssignGuide({{ json_encode($stud) }})" class="text-indigo-600 hover:text-indigo-900">Change</button>
                                                <form action="{{ route('admin.guide-assignments.release', $stud->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-rose-600 hover:text-rose-900">Release</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-8 text-gray-400 italic">No assigned guide records found.</td>
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Batch Directory</h3>
                        <p class="text-sm text-gray-500">Manage academic cohorts, batch guide assignments, and batch transfers.</p>
                    </div>
                    <div>
                        <button @click="openCreateBatch()" class="flex items-center justify-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-xs transition shadow-sm">
                            <i class="fas fa-plus"></i>
                            <span>Create New Batch</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Default Guide</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Students</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Applications</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">NOC Generated</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($batches ?? [] as $batch)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                        <a href="{{ route('admin.batches.show', $batch->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ $batch->name }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($batch->guide)
                                            <span class="font-bold text-gray-800">{{ $batch->guide->name }}</span>
                                        @else
                                            <span class="text-gray-400 italic">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-800">{{ $batch->students_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-800">{{ $batch->approved_apps ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-teal-650">{{ $batch->noc_count ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold space-x-1.5">
                                        <a href="{{ route('admin.batches.show', $batch->id) }}" class="text-indigo-600 hover:text-indigo-900">Manage</a>
                                        <button @click="openEditBatch({{ json_encode($batch) }})" class="text-teal-600 hover:text-teal-900">Edit Name</button>
                                        <form action="{{ route('admin.batches.destroy', $batch->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this batch?')" class="text-rose-600 hover:text-rose-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 6: AUTHORITY MANAGEMENT -->
        <div x-show="activeTab === 'authority_management'" x-transition class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Authority & Permissions Management</h3>
                    <p class="text-sm text-gray-500 font-semibold">Assign permissions (Guide, Approval Faculty, NOC Authority) directly to faculty users.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Faculty Details</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Guide Role</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Approval Faculty</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">NOC Authority</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($faculty ?? [] as $fac)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $fac->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $fac->email }}</div>
                                    </td>
                                    <form action="{{ route('admin.authority-management.update', $fac->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <input type="checkbox" name="permissions[]" value="guide" {{ $fac->hasPermission('guide') ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <input type="checkbox" name="permissions[]" value="approval_faculty" {{ $fac->hasPermission('approval_faculty') ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <input type="checkbox" name="permissions[]" value="noc_authority" {{ $fac->hasPermission('noc_authority') ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                                            <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-lg transition">Update</button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 8: ACCOUNT MANAGEMENT -->
        <div x-show="activeTab === 'account_management'" x-transition class="space-y-6">
            <div x-data="{ subAccountTab: 'students' }" class="space-y-4">
                <div class="flex border-b border-gray-200 gap-2 bg-white p-1.5 rounded-xl shadow-xs">
                    <button @click="subAccountTab = 'students'" :class="subAccountTab === 'students' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition">
                        Registered Students
                    </button>
                    <button @click="subAccountTab = 'faculty'" :class="subAccountTab === 'faculty' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition">
                        Registered Faculty
                    </button>
                    <button @click="subAccountTab = 'pending'" :class="subAccountTab === 'pending' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                        Pending Verification
                        <span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 font-bold text-[9px] rounded-full">{{ count($pendingUsers ?? []) }}</span>
                    </button>
                </div>

                <!-- Tab Registered Students -->
                <div x-show="subAccountTab === 'students'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @forelse($registeredStudents ?? [] as $stud)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $stud->enrollment_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">{{ $stud->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $stud->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                                        <form action="{{ route('admin.account-management.deactivate', $stud->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-rose-600 hover:text-rose-900">Deactivate Account</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-400 italic">No registered student accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tab Registered Faculty -->
                <div x-show="subAccountTab === 'faculty'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Faculty ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @forelse($registeredFaculty ?? [] as $fac)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $fac->faculty_id ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">{{ $fac->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $fac->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                                        <form action="{{ route('admin.account-management.deactivate', $fac->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-rose-600 hover:text-rose-900">Deactivate Account</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-400 italic">No registered faculty accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tab Pending Verification -->
                <div x-show="subAccountTab === 'pending'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Identifier / ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role Requested</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @forelse($pendingUsers ?? [] as $user)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $user->enrollment_number ?: ($user->faculty_id ?: 'N/A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-semibold uppercase tracking-wider text-xs">{{ $user->role?->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                                        <form action="{{ route('admin.account-management.activate', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold px-3 py-1.5 rounded-lg transition mr-1">Approve & Activate</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-400 italic">No users pending activation.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 9: AUDIT LOGS -->
        <div x-show="activeTab === 'audit_logs'" x-transition class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Audit Logs Registry</h3>
                    <p class="text-sm text-gray-500">Monitor all system administration events and modifications in real time.</p>
                </div>

                <!-- Audit log filters -->
                <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="bg-gray-50 p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <select name="action_type" class="w-full bg-white border border-gray-300 rounded-lg py-2 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Action Types</option>
                        @foreach($actionTypes ?? [] as $type)
                            <option value="{{ $type }}" {{ request('action_type') === $type ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>

                    <div class="flex space-x-2">
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-white border border-gray-300 rounded-lg py-1.5 px-2.5 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-white border border-gray-300 rounded-lg py-1.5 px-2.5 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search details..." class="w-full bg-white border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg py-2 transition shadow-sm">Filter</button>
                        <a href="{{ route('admin.audit-logs.index') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs rounded-lg py-2 transition">Clear</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Administrator</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action Description</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Target Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-xs">
                            @forelse($auditLogs ?? [] as $log)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-3 whitespace-nowrap font-bold text-gray-700">{{ $log->timestamp->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap font-semibold text-indigo-850">{{ $log->admin_name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap font-bold uppercase tracking-wider text-[10px] text-gray-600">{{ $log->action_type ?: 'General' }}</td>
                                    <td class="px-6 py-3 text-gray-800 font-medium">{{ $log->action }}</td>
                                    <td class="px-6 py-3 text-gray-500 font-mono text-[10px]">{{ $log->target }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-400 italic">No audit log records matching criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($auditLogs) && method_exists($auditLogs, 'links'))
                    <div class="p-4 bg-white border-t border-gray-100">
                        {{ $auditLogs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 10: SYSTEM DIAGNOSTICS -->
        <div x-show="activeTab === 'system_diagnostics'" x-transition class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ diagnostics: null, loading: true }">
                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4" @vue:mounted="loading = true; fetch('{{ route('admin.system-diagnostics') }}').then(r => r.json()).then(d => { diagnostics = d; loading = false; })">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">System Diagnostics</h3>
                        <p class="text-sm text-gray-500">Live health, extensions, and server module checks.</p>
                    </div>
                </div>

                <div x-show="loading" class="text-center py-12 text-gray-500">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Loading system information...
                </div>

                <div x-show="!loading && diagnostics" class="space-y-4">
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Module / Check</th>
                                    <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Details / Version</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <template x-for="(check, key) in diagnostics" :key="key">
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-bold text-gray-800" x-text="key.toUpperCase().replace(/_/g, ' ')"></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px]" :class="check.status === 'ok' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'" x-text="check.status"></span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-650 font-mono" x-text="check.message"></td>
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
            <div @click="showBatchModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold" x-text="modalMode === 'create' ? 'Create New Batch' : 'Edit Batch Name'"></h3>
                    <button @click="showBatchModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="modalMode === 'create' ? '{{ route('admin.batches.store') }}' : '{{ url('admin/batches') }}/' + batchForm.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Batch Code / Name</label>
                        <input type="text" name="name" x-model="batchForm.name" required placeholder="e.g. 2026-IT" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showBatchModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. STUDENT MODAL (Create/Edit) -->
    <div x-show="showStudentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showStudentModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold" x-text="modalMode === 'create' ? 'Add Student Record' : 'Edit Student Record'"></h3>
                    <button @click="showStudentModal = false" class="text-white/80 hover:text-white transition">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Enrollment Number</label>
                            <input type="text" name="enrollment_number" x-model="studentForm.enrollment_number" required placeholder="e.g. 21IT001" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Student Name</label>
                            <input type="text" name="name" x-model="studentForm.name" required placeholder="e.g. Jane Doe" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" x-model="studentForm.email" required placeholder="student@edu.in" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Department</label>
                            <input type="text" name="department" x-model="studentForm.department" required placeholder="e.g. Information Technology" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Semester</label>
                            <input type="number" name="semester" x-model="studentForm.semester" required min="1" max="10" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Academic Batch</label>
                            <select name="batch_id" x-model="studentForm.batch_id" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">-- Choose Batch (Optional) --</option>
                                @foreach($batches ?? [] as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Assigned Guide / Faculty</label>
                        <select name="guide_id" x-model="studentForm.guide_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">-- Unassigned --</option>
                            @foreach($faculty ?? [] as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }} ({{ $fac->department }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showStudentModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm" x-text="modalMode === 'create' ? 'Save' : 'Update'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. FACULTY MODAL (Create/Edit) -->
    <div x-show="showFacultyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showFacultyModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold" x-text="modalMode === 'create' ? 'Add Faculty Record' : 'Edit Faculty Record'"></h3>
                    <button @click="showFacultyModal = false" class="text-white/80 hover:text-white transition">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Faculty ID</label>
                            <input type="text" name="faculty_id" x-model="facultyForm.faculty_id" required placeholder="e.g. F01" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Faculty Name</label>
                            <input type="text" name="name" x-model="facultyForm.name" required placeholder="e.g. Dr. John Doe" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" x-model="facultyForm.email" required placeholder="faculty@ac.in" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Department</label>
                            <input type="text" name="department" x-model="facultyForm.department" required placeholder="e.g. IT" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Designation</label>
                        <input type="text" name="designation" x-model="facultyForm.designation" required placeholder="e.g. Assistant Professor" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showFacultyModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm" x-text="modalMode === 'create' ? 'Save' : 'Update'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. IMPORT MODAL (Excel Upload) -->
    <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showImportModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold">Import Students Directory</h3>
                    <button @click="showImportModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('admin.student-directory.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Excel (.xlsx) file</label>
                        <input type="file" name="file" required accept=".xlsx" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-gray-50">
                    </div>

                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-xs space-y-2 text-indigo-900 leading-relaxed">
                        <p class="font-bold flex items-center">
                            <i class="fas fa-info-circle mr-1 text-indigo-600"></i>
                            Column Header Guidelines:
                        </p>
                        <div>
                            <p>Required columns: <code class="font-bold font-mono">Name</code>, <code class="font-bold font-mono">Email</code></p>
                            <p class="mt-1">Optional: <code class="font-bold font-mono">Enrollment Number</code>, <code class="font-bold font-mono">Department</code>, <code class="font-bold font-mono">Semester</code>, <code class="font-bold font-mono">Batch</code>, <code class="font-bold font-mono">Assigned Guide</code></p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm">Import List</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 5. MOVE BATCH MODAL -->
    <div x-show="showMoveBatchModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showMoveBatchModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-sm font-bold">Move Batch: <span x-text="moveBatchForm.student_name"></span></h3>
                    <button @click="showMoveBatchModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="'{{ url('admin/student-directory') }}/' + moveBatchForm.student_id + '/move-batch'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Select Batch</label>
                        <select name="batch_id" x-model="moveBatchForm.batch_id" required class="w-full border border-gray-300 rounded-lg py-2 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Choose Batch --</option>
                            @foreach($batches ?? [] as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showMoveBatchModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-xs text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition shadow-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 6. ASSIGN GUIDE MODAL -->
    <div x-show="showAssignGuideModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showAssignGuideModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-sm font-bold">Assign Guide: <span x-text="assignGuideForm.student_name"></span></h3>
                    <button @click="showAssignGuideModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="'{{ url('admin/student-directory') }}/' + assignGuideForm.student_id + '/assign-guide'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Select Faculty Guide</label>
                        <select name="guide_id" x-model="assignGuideForm.guide_id" required class="w-full border border-gray-300 rounded-lg py-2 px-3 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Choose Guide --</option>
                            @foreach($faculty ?? [] as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }} ({{ $fac->department }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showAssignGuideModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-xs text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition shadow-sm">Assign & Lock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 7. STUDENT DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteStudentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showDeleteStudentModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <div class="bg-red-600 px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-exclamation-triangle text-xl animate-pulse"></i>
                        <h3 class="text-lg font-bold">WARNING</h3>
                    </div>
                    <button type="button" @click="showDeleteStudentModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/student-directory') }}/' + deleteStudentData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('DELETE')

                    <p class="text-sm font-semibold text-gray-800">You are about to permanently delete this student record.</p>

                    <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-xs space-y-2 text-red-900">
                        <div>
                            <span class="font-bold text-gray-500 uppercase tracking-wider block text-[9px]">Student Name</span>
                            <span class="font-bold text-sm text-gray-900" x-text="deleteStudentData.name"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-500 uppercase tracking-wider block text-[9px]">Enrollment Number</span>
                            <span class="font-bold font-mono text-sm text-gray-900" x-text="deleteStudentData.enrollment_number"></span>
                        </div>
                    </div>

                    <p class="text-xs text-red-600 font-bold flex items-center">
                        <i class="fas fa-info-circle mr-1 text-red-600"></i>This action cannot be undone.
                    </p>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">To confirm deletion, type the Enrollment Number exactly as shown:</label>
                        <input type="text" name="confirmation_text" x-model="deleteStudentConfirmationInput" required placeholder="Type Enrollment Number" class="w-full border border-gray-300 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-red-500 outline-none font-mono">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showDeleteStudentModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" :disabled="deleteStudentConfirmationInput !== deleteStudentData.enrollment_number" :class="deleteStudentConfirmationInput !== deleteStudentData.enrollment_number ? 'opacity-50 cursor-not-allowed bg-red-400' : 'bg-red-600 hover:bg-red-700 text-white font-bold shadow-sm'" class="px-5 py-2 rounded-lg text-sm font-bold transition">Delete Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 8. FACULTY DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteFacultyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showDeleteFacultyModal = false" class="fixed inset-0 transition-opacity bg-black/50"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <div class="bg-red-600 px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-exclamation-triangle text-xl animate-pulse"></i>
                        <h3 class="text-lg font-bold">WARNING</h3>
                    </div>
                    <button type="button" @click="showDeleteFacultyModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/faculty-directory') }}/' + deleteFacultyData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('DELETE')

                    <p class="text-sm font-semibold text-gray-800">You are about to permanently delete this faculty record.</p>

                    <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-xs space-y-2 text-red-900">
                        <div>
                            <span class="font-bold text-gray-500 uppercase tracking-wider block text-[9px]">Faculty Name</span>
                            <span class="font-bold text-sm text-gray-900" x-text="deleteFacultyData.name"></span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-500 uppercase tracking-wider block text-[9px]">Faculty Email</span>
                            <span class="font-bold text-sm text-gray-900" x-text="deleteFacultyData.email"></span>
                        </div>
                    </div>

                    <p class="text-xs text-red-600 font-bold flex items-center">
                        <i class="fas fa-info-circle mr-1 text-red-600"></i>This action cannot be undone.
                    </p>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">To confirm deletion, type the faculty full name exactly as shown:</label>
                        <input type="text" name="confirmation_text" x-model="deleteFacultyConfirmationInput" required placeholder="Type Faculty Full Name" class="w-full border border-gray-300 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showDeleteFacultyModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" :disabled="deleteFacultyConfirmationInput !== deleteFacultyData.name" :class="deleteFacultyConfirmationInput !== deleteFacultyData.name ? 'opacity-50 cursor-not-allowed bg-red-400' : 'bg-red-600 hover:bg-red-700 text-white font-bold shadow-sm'" class="px-5 py-2 rounded-lg text-sm font-bold transition">Delete Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
