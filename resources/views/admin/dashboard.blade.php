@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12" x-data="{ 
    activeTab: '{{ request('tab', 'students') }}',
    showBatchModal: false,
    showStudentModal: false,
    showImportModal: false,
    showViewBatchStudentsModal: false,
    showDeleteStudentModal: false,
    showDeleteFacultyModal: false,
    modalMode: 'create',
    
    // Form states
    batchForm: { id: null, name: '' },
    studentForm: { id: null, enrollment_number: '', name: '', email: '', department: '', semester: 1, batch_id: '', guide_id: '', password: '' },
    importForm: { type: 'student', file: '' },
    
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
    }
}">

    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm mb-8 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Left Side - Admin Info -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center shadow-md">
                        <i class="fas fa-user-shield text-white text-lg animate-pulse"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-indigo-600 font-semibold uppercase tracking-wider">System Administrator</p>
                    </div>
                </div>

                <!-- Center Title -->
                <div class="hidden md:block">
                    <span class="text-gray-800 font-bold text-lg tracking-wide uppercase">Internship NOC Portal</span>
                </div>

                <!-- Right Side - Logout -->
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

        <!-- Top Notifications / Success Banners -->
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
        @if(session('import_report') && session('import_report')['type'] !== 'Mentor Mapping')
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

        <!-- Statistics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <!-- Students -->
            <div class="bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sky-100 text-xs font-semibold uppercase tracking-wider">Students</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalStudents }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Faculty -->
            <div class="bg-gradient-to-br from-teal-400 to-teal-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-teal-100 text-xs font-semibold uppercase tracking-wider">Faculty</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalFaculty }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Guides -->
            <div class="bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wider">Guides</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalGuides }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-user-tie text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Batches -->
            <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-purple-100 text-xs font-semibold uppercase tracking-wider">Batches</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalBatches }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Applications -->
            <div class="bg-gradient-to-br from-amber-400 to-amber-500 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-amber-100 text-xs font-semibold uppercase tracking-wider">Total Apps</p>
                        <h3 class="text-3xl font-black mt-2">{{ $totalApplications }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-file-signature text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-yellow-100 text-xs font-semibold uppercase tracking-wider">Pending Review</p>
                        <h3 class="text-3xl font-black mt-2">{{ $pendingApplications }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-hourglass-half text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Approved -->
            <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wider">Approved</p>
                        <h3 class="text-3xl font-black mt-2">{{ $approvedApplications }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- NOCs -->
            <div class="bg-gradient-to-br from-rose-400 to-rose-600 rounded-2xl shadow-md p-5 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-rose-100 text-xs font-semibold uppercase tracking-wider">NOCs Generated</p>
                        <h3 class="text-3xl font-black mt-2">{{ $generatedNocs }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2.5">
                        <i class="fas fa-certificate text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (Simplified: 5 -> 3) -->
        <div class="flex flex-wrap border-b border-gray-200 gap-2 mb-8 bg-white p-2 rounded-xl shadow-sm">
            <button 
                @click="activeTab = 'students'" 
                :class="activeTab === 'students' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                class="flex items-center space-x-2 px-5 py-3 rounded-lg text-sm font-semibold transition duration-150">
                <i class="fas fa-user-graduate text-sm"></i>
                <span>Student Management</span>
            </button>
            <button 
                @click="activeTab = 'mentor_mapping'" 
                :class="activeTab === 'mentor_mapping' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                class="flex items-center space-x-2 px-5 py-3 rounded-lg text-sm font-semibold transition duration-150">
                <i class="fas fa-map-marked-alt text-sm"></i>
                <span>Mentor Mapping</span>
            </button>
            <button 
                @click="activeTab = 'batches'" 
                :class="activeTab === 'batches' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                class="flex items-center space-x-2 px-5 py-3 rounded-lg text-sm font-semibold transition duration-150">
                <i class="fas fa-layer-group text-sm"></i>
                <span>Batch Directory</span>
            </button>
            <button 
                @click="activeTab = 'faculty_authority'" 
                :class="activeTab === 'faculty_authority' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                class="flex items-center space-x-2 px-5 py-3 rounded-lg text-sm font-semibold transition duration-150">
                <i class="fas fa-users-cog text-sm"></i>
                <span>Faculty Authority System</span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB CONTENTS -->
        <!-- ============================================== -->

        <!-- TAB 1: STUDENT MANAGEMENT -->
        <div x-show="activeTab === 'students'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Student Management</h3>
                        <p class="text-sm text-gray-500">Query and filter student details, academic batches, assigned guides and applications</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="openImport('student')" class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-semibold text-sm transition shadow-sm">
                            <i class="fas fa-file-import text-indigo-600"></i>
                            <span>Import Students List</span>
                        </button>
                        <button @click="openCreateStudent()" class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm transition shadow-sm">
                            <i class="fas fa-plus"></i>
                            <span>Add Student Manually</span>
                        </button>
                    </div>
                </div>

                <!-- Filters & Search Form -->
                <div class="p-5 bg-gray-50 border-b border-gray-100">
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                        <input type="hidden" name="tab" value="students">

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Search Text</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input 
                                    type="text" 
                                    name="student_search" 
                                    value="{{ request('student_search') }}" 
                                    placeholder="Name, enrollment..." 
                                    class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Batch</label>
                            <select name="batch_id" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">All Batches</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Assigned Guide</label>
                            <select name="guide_id" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">All Guides</option>
                                @foreach($faculty as $fac)
                                    <option value="{{ $fac->id }}" {{ request('guide_id') == $fac->id ? 'selected' : '' }}>{{ $fac->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Application Status</label>
                            <select name="app_status" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('app_status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="approved" {{ request('app_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('app_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="noc_generated" {{ request('app_status') == 'noc_generated' ? 'selected' : '' }}>NOC Generated</option>
                                <option value="no_application" {{ request('app_status') == 'no_application' ? 'selected' : '' }}>No Application</option>
                            </select>
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm text-center">
                                Filter
                            </button>
                            @if(request('student_search') || request('batch_id') || request('guide_id') || request('app_status') || request('department'))
                                <a href="{{ route('admin.dashboard', ['tab' => 'students']) }}" class="py-2 px-3 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition text-center">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                @if($students->isEmpty())
                    <div class="text-center py-16 bg-white">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                            <i class="fas fa-user-graduate text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No students found matching the query.</p>
                        <button @click="openCreateStudent()" class="mt-4 text-indigo-600 hover:text-indigo-800 font-semibold text-sm">Add a student manually &rarr;</button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment No.</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Guide Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Application Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NOC Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($students as $stud)
                                    @php
                                        $latestApp = $stud->internshipApplications->first();
                                    @endphp
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                            {{ $stud->enrollment_number ?: 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $stud->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $stud->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($stud->batch)
                                                <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-md">{{ $stud->batch->name }}</span>
                                            @else
                                                <span class="text-xs text-gray-400">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($stud->guide)
                                                <div class="text-sm font-bold text-gray-800">{{ $stud->guide->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $stud->guide->email }}</div>
                                            @else
                                                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 border border-amber-100 rounded-md">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($latestApp)
                                                @if($latestApp->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1 text-[10px]"></i>Pending Review
                                                    </span>
                                                @elseif($latestApp->status === 'faculty_approved')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1 text-[10px]"></i>Faculty Approved
                                                    </span>
                                                @elseif($latestApp->status === 'pending_higher')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                        <i class="fas fa-hourglass-half mr-1 text-[10px]"></i>Pending Higher Review
                                                    </span>
                                                @elseif($latestApp->status === 'higher_faculty_approved' || $latestApp->status === 'noc_generated')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                        <i class="fas fa-check-double mr-1 text-[10px]"></i>Approved
                                                    </span>
                                                @elseif(in_array($latestApp->status, ['faculty_rejected', 'higher_faculty_rejected']))
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1 text-[10px]"></i>Rejected
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400 font-semibold italic">Not Applied</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($latestApp && $latestApp->noc)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                                                    <i class="fas fa-file-pdf mr-1.5 text-[10px]"></i>NOC Generated
                                                </span>
                                            @elseif($latestApp && $latestApp->noc_requested)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-paper-plane mr-1.5 text-[10px]"></i>NOC Requested
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 font-semibold">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-3">
                                                <button @click="openEditStudent(@json($stud))" class="text-indigo-600 hover:text-indigo-950 transition" title="Edit Student">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <button @click="openDeleteStudent(@json($stud))" class="text-red-600 hover:text-red-900 transition" title="Delete Student">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 2: MENTOR MAPPING (MASTER MODULE) -->
        <div x-show="activeTab === 'mentor_mapping'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
            
            <!-- Mentor Mapping Import Report Banner -->
            @if(session('import_report') && session('import_report')['type'] === 'Mentor Mapping')
                <div class="bg-white rounded-xl border border-emerald-200 shadow-lg overflow-hidden mb-6">
                    <div class="bg-indigo-900 px-6 py-4 flex justify-between items-center text-white">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <i class="fas fa-check-circle text-xl text-emerald-400"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold">Import Successful</h4>
                                <p class="text-xs text-indigo-200">{{ session('import_report')['upload_date'] ?? 'Just now' }}</p>
                            </div>
                        </div>
                        <button class="text-white/80 hover:text-white transition" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 p-6">
                        <div class="bg-gray-50 border border-gray-150 rounded-xl p-4 text-center">
                            <p class="text-gray-500 font-bold uppercase text-[10px]">Students Processed</p>
                            <h4 class="text-2xl font-black text-gray-800 mt-1">{{ session('import_report')['total_rows'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-sky-50 border border-sky-100 rounded-xl p-4 text-center">
                            <p class="text-sky-600 font-bold uppercase text-[10px]">Students Created</p>
                            <h4 class="text-2xl font-black text-sky-700 mt-1">{{ session('import_report')['created_students'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-center">
                            <p class="text-indigo-600 font-bold uppercase text-[10px]">Faculty Created</p>
                            <h4 class="text-2xl font-black text-indigo-700 mt-1">{{ session('import_report')['created_faculty'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
                            <p class="text-emerald-600 font-bold uppercase text-[10px]">Mappings Created</p>
                            <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ session('import_report')['mappings_created'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-teal-50 border border-teal-100 rounded-xl p-4 text-center">
                            <p class="text-teal-600 font-bold uppercase text-[10px]">Mappings Updated</p>
                            <h4 class="text-2xl font-black text-teal-700 mt-1">{{ session('import_report')['mappings_updated'] ?? 0 }}</h4>
                        </div>
                        <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 text-center">
                            <p class="text-rose-600 font-bold uppercase text-[10px]">Warnings</p>
                            <h4 class="text-2xl font-black text-rose-700 mt-1">{{ (session('import_report')['failed'] ?? 0) + count(session('import_report')['errors'] ?? []) }}</h4>
                        </div>
                    </div>
                    
                    @if(session('mentor_mapping_created_accounts') && count(session('mentor_mapping_created_accounts')) > 0)
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-3">
                            <div class="text-xs text-gray-500 font-medium flex items-center">
                                <i class="fas fa-info-circle text-indigo-500 mr-2 text-sm"></i>
                                New student or faculty accounts were generated. You can download the report for security records.
                            </div>
                            <a href="{{ route('admin.mentor-mapping.download-report') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                                <i class="fas fa-file-download mr-1.5"></i> Download Created Accounts Report
                            </a>
                        </div>
                    @endif

                    @if(!empty(session('import_report')['errors']) && count(session('import_report')['errors']) > 0)
                        <div class="px-6 pb-6">
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

            <!-- XLSX Preview Mode -->
            @if(session('mentor_mapping_preview'))
                <!-- Preview Statistics Summary -->
                @if(session('mentor_mapping_stats'))
                    @php $stats = session('mentor_mapping_stats'); @endphp
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
                        <div class="bg-white border border-sky-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-sky-400 font-bold uppercase">Total Sheets</p>
                            <p class="text-2xl font-black text-sky-600 mt-1">{{ $stats['total_sheets'] ?? '?' }}</p>
                            <p class="text-[9px] text-gray-400 mt-0.5">{{ $stats['processed_sheets'] ?? 0 }} processed, {{ $stats['skipped_sheets'] ?? 0 }} skipped</p>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Total Rows</p>
                            <p class="text-2xl font-black text-gray-800 mt-1">{{ $stats['total_rows'] }}</p>
                        </div>
                        <div class="bg-white border border-emerald-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-emerald-500 font-bold uppercase">Existing Students</p>
                            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['students_found'] }}</p>
                        </div>
                        <div class="bg-white border border-indigo-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-indigo-500 font-bold uppercase">New Students</p>
                            <p class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['new_students'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white border border-teal-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-teal-500 font-bold uppercase">Detected Mentors</p>
                            <p class="text-2xl font-black text-teal-600 mt-1">{{ $stats['detected_mentors'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                        <div class="bg-white border border-indigo-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-indigo-500 font-bold uppercase">New Faculty</p>
                            <p class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['new_faculty'] }}</p>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-gray-500 font-bold uppercase">Existing Faculty</p>
                            <p class="text-2xl font-black text-gray-600 mt-1">{{ $stats['existing_faculty'] }}</p>
                        </div>
                        <div class="bg-white border border-purple-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-purple-500 font-bold uppercase">Detected Batches</p>
                            <p class="text-2xl font-black text-purple-600 mt-1">{{ $stats['detected_batches'] ?? $stats['new_batches'] }}</p>
                        </div>
                        <div class="bg-white border border-rose-200 rounded-xl p-4 text-center shadow-sm">
                            <p class="text-[10px] text-rose-500 font-bold uppercase">Validation Errors</p>
                            <p class="text-2xl font-black text-rose-600 mt-1">{{ $stats['validation_errors'] }}</p>
                        </div>
                    </div>
                @endif

                <!-- Sheet Column Mapping Info -->
                @if(session('mentor_mapping_sheet_mappings') && count(session('mentor_mapping_sheet_mappings')) > 0)
                    <div class="bg-white border border-indigo-100 rounded-xl shadow-sm mb-6 overflow-hidden" x-data="{ showMappings: false }">
                        <button @click="showMappings = !showMappings" class="w-full px-6 py-3 flex items-center justify-between text-left bg-indigo-50 hover:bg-indigo-100/70 transition">
                            <span class="text-xs font-bold text-indigo-800 flex items-center">
                                <i class="fas fa-columns mr-2 text-indigo-500"></i>
                                Detected Column Mappings ({{ count(session('mentor_mapping_sheet_mappings')) }} sheet(s))
                            </span>
                            <i class="fas fa-chevron-down text-indigo-400 text-xs transition-transform" :class="showMappings && 'rotate-180'"></i>
                        </button>
                        <div x-show="showMappings" x-transition class="px-6 py-4 space-y-3 border-t border-indigo-100">
                            @foreach(session('mentor_mapping_sheet_mappings') as $sm)
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-150">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-gray-800">
                                            <i class="fas fa-file-excel text-emerald-500 mr-1.5"></i>Sheet: "{{ $sm['sheet_name'] }}"
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-medium">Header detected at row {{ $sm['header_row'] }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($sm['mapped_columns'] as $col)
                                            <div class="inline-flex items-center text-[10px] bg-white border border-gray-200 rounded-md px-2 py-1 shadow-xs">
                                                <span class="font-bold text-gray-600">"{{ $col['original'] }}"</span>
                                                <i class="fas fa-arrow-right mx-1.5 text-indigo-400 text-[8px]"></i>
                                                <span class="font-bold text-indigo-600">{{ $col['mapped_to'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-xl border border-indigo-200 shadow-lg overflow-hidden">
                    <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                        <div>
                            <h3 class="text-lg font-bold flex items-center">
                                <i class="fas fa-search-location mr-3"></i>Confirm Mentor Mappings
                            </h3>
                            <p class="text-xs text-indigo-100 mt-1">Please review the parsed rows before finalizing the database transaction</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.dashboard', ['tab' => 'mentor_mapping']) }}" class="px-4 py-2 border border-white/30 text-white rounded-lg text-xs font-bold hover:bg-white/10 transition">
                                Cancel
                            </a>
                            <form action="{{ route('admin.mentor-mapping.confirm') }}" method="POST">
                                @csrf
                                <input type="hidden" name="mappings_json" value="{{ session('mentor_mapping_json') }}">
                                <input type="hidden" name="file_name" value="{{ session('mentor_mapping_file_name') }}">
                                <input type="hidden" name="file_path" value="{{ session('mentor_mapping_file_path') }}">
                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow">
                                    <i class="fas fa-check-double mr-1.5"></i>Apply Mentor Mappings
                                </button>
                            </form>
                        </div>
                    </div>
 
                    @if(session('mentor_mapping_warnings') && count(session('mentor_mapping_warnings')) > 0)
                        <div class="bg-amber-50 border-b border-amber-200 px-6 py-3 flex items-start text-xs text-amber-800">
                            <i class="fas fa-info-circle mr-2 mt-0.5 text-sm text-amber-600"></i>
                            <div>
                                <span class="font-bold">File Parsing Notice:</span> Some rows contain missing values or formatting anomalies:
                                <div class="max-h-24 overflow-y-auto mt-1 list-disc list-inside">
                                    @foreach(session('mentor_mapping_warnings') as $warn)
                                        <div class="py-0.5 font-medium">{{ $warn }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
 
                    <!-- Import Comparison Differences -->
                    @if(session('mentor_mapping_comparison'))
                        @php $comp = session('mentor_mapping_comparison'); @endphp
                        <div class="bg-gray-50 border-b border-gray-200 p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-white border border-gray-150 rounded-xl p-3 shadow-xs">
                                <p class="text-[10px] text-indigo-500 font-bold uppercase">Students Added</p>
                                <p class="text-xl font-black text-indigo-650 mt-1">{{ count($comp['added']) }}</p>
                                @if(count($comp['added']) > 0)
                                    <div class="text-[9px] text-gray-500 mt-1 max-h-20 overflow-y-auto space-y-0.5">
                                        @foreach(array_slice($comp['added'], 0, 5) as $item)
                                            <div>+ {{ $item['name'] }} ({{ $item['enrollment'] }})</div>
                                        @endforeach
                                        @if(count($comp['added']) > 5) <div class="text-gray-400">...and {{ count($comp['added']) - 5 }} more</div> @endif
                                    </div>
                                @endif
                            </div>
                            <div class="bg-white border border-gray-150 rounded-xl p-3 shadow-xs">
                                <p class="text-[10px] text-rose-500 font-bold uppercase">Students Removed</p>
                                <p class="text-xl font-black text-rose-605 mt-1">{{ count($comp['removed']) }}</p>
                                @if(count($comp['removed']) > 0)
                                    <div class="text-[9px] text-gray-500 mt-1 max-h-20 overflow-y-auto space-y-0.5">
                                        @foreach(array_slice($comp['removed'], 0, 5) as $item)
                                            <div>- {{ $item['name'] }} ({{ $item['enrollment'] }})</div>
                                        @endforeach
                                        @if(count($comp['removed']) > 5) <div class="text-gray-400">...and {{ count($comp['removed']) - 5 }} more</div> @endif
                                    </div>
                                @endif
                            </div>
                            <div class="bg-white border border-gray-150 rounded-xl p-3 shadow-xs">
                                <p class="text-[10px] text-amber-500 font-bold uppercase">Guides Changed</p>
                                <p class="text-xl font-black text-amber-600 mt-1">{{ count($comp['guide_changes']) }}</p>
                                @if(count($comp['guide_changes']) > 0)
                                    <div class="text-[9px] text-gray-500 mt-1 max-h-20 overflow-y-auto space-y-0.5">
                                        @foreach(array_slice($comp['guide_changes'], 0, 5) as $item)
                                            <div>{{ $item['name'] }}: {{ $item['old_guide'] }} &rarr; {{ $item['new_guide'] }}</div>
                                        @endforeach
                                        @if(count($comp['guide_changes']) > 5) <div class="text-gray-400">...and {{ count($comp['guide_changes']) - 5 }} more</div> @endif
                                    </div>
                                @endif
                            </div>
                            <div class="bg-white border border-gray-150 rounded-xl p-3 shadow-xs">
                                <p class="text-[10px] text-purple-500 font-bold uppercase">Batch Changes</p>
                                <p class="text-xl font-black text-purple-650 mt-1">{{ count($comp['batch_changes']) }}</p>
                                @if(count($comp['batch_changes']) > 0)
                                    <div class="text-[9px] text-gray-500 mt-1 max-h-20 overflow-y-auto space-y-0.5">
                                        @foreach(array_slice($comp['batch_changes'], 0, 5) as $item)
                                            <div>{{ $item['name'] }}: {{ $item['old_batch'] }} &rarr; {{ $item['new_batch'] }}</div>
                                        @endforeach
                                        @if(count($comp['batch_changes']) > 5) <div class="text-gray-400">...and {{ count($comp['batch_changes']) - 5 }} more</div> @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Detected Mentor</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach(session('mentor_mapping_preview') as $row)
                                    <tr class="hover:bg-gray-50/50 transition-colors text-sm">
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700">
                                            {{ $row['enrollment'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">
                                            {{ $row['student_name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800 flex items-center">
                                            <i class="fas fa-chalkboard-teacher text-indigo-500 mr-2 text-xs"></i>
                                            {{ $row['mentor_name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                                            @if(!$row['student_exists'])
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    <i class="fas fa-user-plus mr-1.5 text-[10px]"></i>Create Student & Mapping
                                                </span>
                                            @elseif($row['status'] === 'Update Assignment')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                                    <i class="fas fa-sync-alt mr-1.5 text-[10px]"></i>Update Mapping
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-md font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    <i class="fas fa-plus mr-1.5 text-[10px]"></i>Create Mapping
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Normal Mode: File Upload & Dual Directory Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Upload Dropzone Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden lg:col-span-2">
                    <div class="bg-indigo-600 p-5 flex justify-between items-center text-white">
                            <h3 class="text-lg font-bold flex items-center">
                                <i class="fas fa-file-excel mr-2.5"></i>
                                Upload Mentor Mapping File
                            </h3>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.mentor-mapping.archives') }}" class="text-xs font-semibold bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg border border-white/20 transition flex items-center gap-1.5 text-white">
                                    <i class="fas fa-archive"></i>
                                    View Archive
                                </a>
                                <span class="text-xs font-semibold bg-white/20 px-2 py-0.5 rounded">XLSX Only</span>
                            </div>
                        </div>
                        <form action="{{ route('admin.mentor-mapping.preview') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                            @csrf
                            
                            <div class="border-2 border-dashed border-indigo-200 rounded-xl p-8 text-center bg-indigo-50/20 hover:bg-indigo-50/40 hover:border-indigo-400 transition cursor-pointer relative">
                                <input type="file" name="file" required accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="document.getElementById('fileNameSpan').innerText = this.files[0] ? this.files[0].name : 'Choose a file...';">
                                <div class="space-y-3">
                                    <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center mx-auto text-indigo-600">
                                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                    </div>
                                    <p class="font-bold text-indigo-950 text-sm">Drag and drop your Excel file here, or <span class="text-indigo-600 underline">browse files</span></p>
                                    <p class="text-xs text-gray-500">Only Excel (.xlsx) files supported — max 5MB</p>
                                </div>
                                <div class="mt-4 p-2 bg-indigo-600/5 rounded-lg border border-indigo-100 inline-block">
                                    <span id="fileNameSpan" class="text-xs font-semibold text-indigo-950 flex items-center justify-center">
                                        <i class="fas fa-file-alt mr-2 text-indigo-600"></i>No file selected
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm flex items-center">
                                    <i class="fas fa-search-location mr-2"></i>
                                    Preview Mapping
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Spreadsheet Requirements & Account Creation Rules Card -->
                    <div class="space-y-6 lg:col-span-1">
                        <!-- Spreadsheet Requirements -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                            <h4 class="text-indigo-950 font-bold flex items-center border-b border-gray-100 pb-3">
                                <i class="fas fa-info-circle text-indigo-600 mr-2 text-lg"></i>
                                Spreadsheet Requirements
                            </h4>
                            
                            <div class="space-y-4 text-xs font-medium text-gray-600">
                                <p class="text-gray-750">Upload an Excel (.xlsx) file containing:</p>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-150 space-y-2">
                                    <p class="font-bold text-gray-800">Required Fields:</p>
                                    <ul class="list-disc list-inside space-y-1 text-gray-600">
                                        <li>Student Enrollment Number / Student ID</li>
                                        <li>Student Name</li>
                                        <li>Mentor / Counsellor Name</li>
                                    </ul>
                                </div>

                                <div class="space-y-2">
                                    <p class="font-bold text-gray-800">The system will automatically:</p>
                                    <ul class="space-y-1.5 text-gray-650">
                                        <li class="flex items-center text-emerald-800"><i class="fas fa-check mr-2 text-emerald-500"></i>Detect existing students</li>
                                        <li class="flex items-center text-emerald-800"><i class="fas fa-check mr-2 text-emerald-500"></i>Create missing student accounts</li>
                                        <li class="flex items-center text-emerald-800"><i class="fas fa-check mr-2 text-emerald-500"></i>Detect existing faculty</li>
                                        <li class="flex items-center text-emerald-800"><i class="fas fa-check mr-2 text-emerald-500"></i>Create missing faculty accounts</li>
                                        <li class="flex items-center text-emerald-800"><i class="fas fa-check mr-2 text-emerald-500"></i>Assign mentor mappings</li>
                                        <li class="flex items-center text-emerald-800"><i class="fas fa-check mr-2 text-emerald-500"></i>Update existing mappings</li>
                                        <li class="flex items-center text-emerald-800"><i class="fas fa-check mr-2 text-emerald-500"></i>Organize students under their assigned guides</li>
                                    </ul>
                                </div>

                                <div>
                                    <p class="font-bold text-gray-800 mb-1">Supported File Type:</p>
                                    <span class="bg-emerald-50 border border-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded flex items-center w-fit"><i class="fas fa-check-circle mr-1 text-[10px]"></i>Excel (.xlsx)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Account Creation Rules -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                            <h4 class="text-indigo-950 font-bold flex items-center border-b border-gray-100 pb-3">
                                <i class="fas fa-user-plus text-indigo-600 mr-2 text-lg"></i>
                                Account Creation Rules
                            </h4>
                            
                            <div class="space-y-4 text-xs text-gray-600">
                                <!-- Student Accounts -->
                                <div class="border-b border-gray-100 pb-3">
                                    <h5 class="font-bold text-indigo-900 mb-1 flex items-center">
                                        <i class="fas fa-user-graduate mr-1.5 text-indigo-500"></i>Student Accounts
                                    </h5>
                                    <p class="text-[11px] text-gray-500 mb-2">If a student does not already exist:</p>
                                    <div class="space-y-1.5 bg-gray-50 p-2.5 rounded-lg border border-gray-150">
                                        <p><span class="font-semibold text-gray-700">Email Format:</span> <code class="bg-white px-1 py-0.5 rounded text-indigo-700 border border-gray-100 font-mono text-[10px]">&lt;EnrollmentNumber&gt;@charusat.edu.in</code></p>
                                        <p><span class="font-semibold text-gray-700">Example:</span> <a href="mailto:23IT001@charusat.edu.in" class="text-indigo-600 underline">23IT001@charusat.edu.in</a></p>
                                        <p><span class="font-semibold text-gray-700">Default Password:</span> Enrollment Number</p>
                                        <p><span class="font-semibold text-gray-700">Example:</span> <code class="bg-white px-1 py-0.5 rounded text-gray-800 border border-gray-100 font-mono text-[10px]">23IT001</code></p>
                                    </div>
                                </div>

                                <!-- Faculty Accounts -->
                                <div class="border-b border-gray-100 pb-3">
                                    <h5 class="font-bold text-indigo-900 mb-1 flex items-center">
                                        <i class="fas fa-chalkboard-teacher mr-1.5 text-indigo-500"></i>Faculty Accounts
                                    </h5>
                                    <p class="text-[11px] text-gray-500 mb-2">If a mentor/faculty does not already exist:</p>
                                    <div class="space-y-1.5 bg-gray-50 p-2.5 rounded-lg border border-gray-150">
                                        <p><span class="font-semibold text-gray-700">Email Format:</span> <code class="bg-white px-1 py-0.5 rounded text-indigo-700 border border-gray-100 font-mono text-[10px]">lowercase(fullname).it@charusat.ac.in</code></p>
                                        <p><span class="font-semibold text-gray-700">Example:</span> Bimal Patel &rarr; <a href="mailto:bimalpatel.it@charusat.ac.in" class="text-indigo-600 underline font-semibold">bimalpatel.it@charusat.ac.in</a></p>
                                        <p><span class="font-semibold text-gray-700">Default Password:</span> FirstName123</p>
                                        <p><span class="font-semibold text-gray-700">Example:</span> <code class="bg-white px-1 py-0.5 rounded text-gray-800 border border-gray-100 font-mono text-[10px]">Bimal123</code></p>
                                    </div>
                                </div>

                                <!-- Security Notice -->
                                <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 text-amber-850">
                                    <h5 class="font-bold mb-1 flex items-center text-amber-900">
                                        <i class="fas fa-shield-alt mr-1.5 text-amber-600"></i>Security Notice
                                    </h5>
                                    <p class="text-[11px] text-amber-800 mb-2 font-medium">All automatically created accounts must:</p>
                                    <ul class="space-y-1 font-semibold text-[11px] text-amber-900 list-inside list-disc">
                                        <li>Change password on first login</li>
                                        <li>Keep existing mappings and permissions</li>
                                        <li>Continue using assigned guide/faculty roles</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- System Diagnostics Panel -->
                        <div class="border-t border-gray-100 pt-4 mt-4" x-data="{ diagnostics: null, loading: false, showDiag: false }">
                            <button @click="showDiag = !showDiag; if(!diagnostics && showDiag) { loading = true; fetch('{{ route('admin.system-diagnostics') }}').then(r => r.json()).then(d => { diagnostics = d; loading = false; }).catch(() => { loading = false; }); }" class="flex items-center justify-between w-full text-xs font-bold text-gray-700 hover:text-indigo-600 transition">
                                <span class="flex items-center"><i class="fas fa-stethoscope mr-2 text-indigo-500"></i>System Diagnostics</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="showDiag && 'rotate-180'"></i>
                            </button>
                            <div x-show="showDiag" x-transition class="mt-3 space-y-1.5">
                                <template x-if="loading">
                                    <div class="text-center py-4 text-gray-400 text-xs">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Loading diagnostics...
                                    </div>
                                </template>
                                <template x-if="diagnostics && !loading">
                                    <div class="space-y-1.5">
                                        <template x-for="(check, key) in diagnostics" :key="key">
                                            <div class="flex items-center justify-between text-xs p-2 rounded-lg" :class="check.status ? 'bg-emerald-50 border border-emerald-100' : 'bg-rose-50 border border-rose-100'">
                                                <span class="font-bold" :class="check.status ? 'text-emerald-700' : 'text-rose-700'">
                                                    <span x-text="check.status ? '✅' : '❌'"></span>
                                                    <span x-text="check.label" class="ml-1"></span>
                                                </span>
                                                <span class="text-[10px] font-medium" :class="check.status ? 'text-emerald-600' : 'text-rose-600'" x-text="check.detail"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lower Section: Read-Only Faculty Directory & Import History -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Read-Only Faculty Directory -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden lg:col-span-2" x-data="{ facultySearch: '' }">
                        <div class="bg-white p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-md font-bold text-gray-900">Faculty Directory</h3>
                                <p class="text-xs text-gray-500">Auto-populated guide listings and mentee aggregates (Read-Only)</p>
                            </div>
                            <div class="relative w-full sm:w-64">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input 
                                    type="text" 
                                    x-model="facultySearch" 
                                    placeholder="Search faculty name or email..." 
                                    class="w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>

                        <div class="overflow-x-auto max-h-[350px]">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Faculty Details</th>
                                        <th class="px-6 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Students</th>
                                        <th class="px-6 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Batches</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($faculty as $fac)
                                        <tr 
                                            x-show="facultySearch === '' || '{{ strtolower($fac->name) }}'.includes(facultySearch.toLowerCase()) || '{{ strtolower($fac->email) }}'.includes(facultySearch.toLowerCase())"
                                            class="hover:bg-gray-50/80 transition-colors text-sm">
                                            <td class="px-6 py-3 whitespace-nowrap">
                                                <div class="font-bold text-gray-900">{{ $fac->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $fac->email }}</div>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $fac->students_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                                                    {{ $fac->students_count }} students
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-xs font-semibold text-gray-600">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700">
                                                    {{ $fac->batches_count }} batches
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Import History Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h4 class="text-indigo-950 font-bold flex items-center border-b border-gray-100 pb-3">
                            <i class="fas fa-history text-indigo-600 mr-2 text-lg"></i>
                            Import History
                        </h4>

                        @php
                            $report = session('import_report') ?? session('mentor_mapping_last_report');
                        @endphp

                        @if($report)
                            <div class="space-y-4 text-xs font-semibold text-gray-600">
                                <div class="bg-gray-50 border border-gray-150 rounded-xl p-3.5 space-y-2.5">
                                    <div class="flex justify-between items-center text-[10px] text-gray-400 uppercase">
                                        <span>Last successful import</span>
                                        <span>{{ $report['upload_date'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2.5">
                                        <div class="bg-white border border-gray-100 rounded-lg p-2.5 shadow-xs">
                                            <p class="text-[10px] text-gray-400">Imported Students</p>
                                            <p class="text-md font-bold text-gray-800 mt-0.5">{{ $report['total_rows'] }}</p>
                                        </div>
                                        <div class="bg-white border border-gray-100 rounded-lg p-2.5 shadow-xs">
                                            <p class="text-[10px] text-emerald-500">Mapped Students</p>
                                            <p class="text-md font-bold text-emerald-600 mt-0.5">{{ $report['success'] }}</p>
                                        </div>
                                        <div class="bg-white border border-gray-100 rounded-lg p-2.5 shadow-xs">
                                            <p class="text-[10px] text-indigo-500">Created Faculty</p>
                                            <p class="text-md font-bold text-indigo-600 mt-0.5">{{ $report['created_faculty'] }}</p>
                                        </div>
                                        <div class="bg-white border border-gray-100 rounded-lg p-2.5 shadow-xs">
                                            <p class="text-[10px] text-blue-500">Updated Faculty</p>
                                            <p class="text-md font-bold text-blue-600 mt-0.5">{{ $report['updated_faculty'] ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white border border-gray-100 rounded-lg p-2.5 shadow-xs">
                                            <p class="text-[10px] text-purple-500">Created Batches</p>
                                            <p class="text-md font-bold text-purple-600 mt-0.5">{{ $report['created_batches'] ?? 0 }}</p>
                                        </div>
                                        <div class="bg-white border border-gray-100 rounded-lg p-2.5 shadow-xs">
                                            <p class="text-[10px] text-amber-500">Warnings</p>
                                            <p class="text-md font-bold text-amber-600 mt-0.5">{{ count($report['errors'] ?? []) }}</p>
                                        </div>
                                        <div class="bg-white border border-gray-100 rounded-lg p-2.5 shadow-xs col-span-2">
                                            <p class="text-[10px] text-rose-500">Failed Records</p>
                                            <p class="text-md font-bold text-rose-600 mt-0.5">{{ $report['failed'] }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(!empty($report['errors']) && count($report['errors']) > 0)
                                    <div class="mt-2">
                                        <p class="text-[10px] font-bold text-amber-700 uppercase mb-1">Warnings Summary log:</p>
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 max-h-24 overflow-y-auto text-[10px] text-amber-800 font-mono space-y-0.5">
                                            @foreach($report['errors'] as $err)
                                                <p>&bull; {{ $err }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-400">
                                <i class="fas fa-clipboard-list text-3xl mb-2.5 block text-gray-300"></i>
                                <p class="text-xs font-semibold">No recent import statistics recorded</p>
                                <p class="text-[10px] text-gray-500 mt-1 max-w-xs mx-auto">Upload a new Excel (.xlsx) file above to establish the mappings history log.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- TAB 3: BATCH DIRECTORY -->
        <div x-show="activeTab === 'batches'" x-transition:enter="transition ease-out duration-200" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Batch Directory</h3>
                        <p class="text-sm text-gray-500">Manage academic cohorts and view drilldown internship statistics</p>
                    </div>
                    <button @click="openCreateBatch()" class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm transition shadow-sm self-start sm:self-center">
                        <i class="fas fa-plus"></i>
                        <span>Create New Batch</span>
                    </button>
                </div>
                
                @if($batches->isEmpty())
                    <div class="text-center py-16 bg-white">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                            <i class="fas fa-layer-group text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No batches created yet.</p>
                        <button @click="openCreateBatch()" class="mt-4 text-indigo-600 hover:text-indigo-800 font-semibold text-sm">Create your first batch &rarr;</button>
                    </div>
                @else
                    <!-- High-End ERP Grid Layout -->
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-gray-50/50">
                        @foreach($batches as $batch)
                            <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-950 text-md">{{ $batch->name }}</h4>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Academic Cohort</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-1.5">
                                            <button @click="openEditBatch(@json($batch))" class="text-gray-400 hover:text-gray-600 p-1 transition" title="Edit Batch">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <form action="{{ route('admin.batches.destroy', $batch->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this batch? All mapped students will be unlinked from it.')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-rose-600 p-1 transition" title="Delete Batch">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="text-xs text-gray-600 space-y-1 bg-indigo-50/20 border border-indigo-100/30 p-3 rounded-xl">
                                        <span class="font-bold text-gray-700">Guide:</span> 
                                        <span class="font-black text-indigo-700">{{ $batch->guide?->name ?? 'None Assigned' }}</span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3.5">
                                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                            <p class="text-[10px] text-gray-400 font-bold uppercase">Students</p>
                                            <p class="text-lg font-black text-gray-800 mt-0.5">{{ $batch->students_count }}</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                            <p class="text-[10px] text-gray-400 font-bold uppercase">Applications</p>
                                            <p class="text-lg font-black text-gray-800 mt-0.5">{{ $batch->pending_apps + $batch->approved_apps }}</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                            <p class="text-[10px] text-gray-400 font-bold uppercase">Approved</p>
                                            <p class="text-lg font-black text-gray-800 mt-0.5">{{ $batch->approved_apps }}</p>
                                        </div>
                                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                                            <p class="text-[10px] text-gray-400 font-bold uppercase">NOC Generated</p>
                                            <p class="text-lg font-black text-gray-800 mt-0.5">{{ $batch->noc_count }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-5 mt-4 border-t border-gray-100 flex gap-2">
                                    <a href="{{ route('admin.batches.show', $batch->id) }}" class="flex-1 text-center py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl transition shadow-xs flex items-center justify-center gap-1.5">
                                        <i class="fas fa-eye text-[10px]"></i> View Details
                                    </a>
                                    <a href="{{ route('admin.batches.show', $batch->id) }}" class="flex-1 text-center py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                                        <i class="fas fa-tasks text-[10px]"></i> Manage Batch
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 4: FACULTY AUTHORITY SYSTEM -->
        <div x-show="activeTab === 'faculty_authority'" x-transition:enter="transition ease-out duration-200" class="space-y-6" style="display: none;">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-white p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Faculty Authority System</h3>
                        <p class="text-sm text-gray-500">Configure and manage database-driven role/authority assignments for all faculty members</p>
                    </div>
                </div>

                <!-- Faculty Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase tracking-wider border-b border-gray-200">
                                <th class="px-6 py-4">Faculty Member</th>
                                <th class="px-6 py-4">Contact Info</th>
                                <th class="px-6 py-4">Designation</th>
                                <th class="px-6 py-4">Assigned Cohorts</th>
                                <th class="px-6 py-4">Authority Level</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 text-sm">
                            @foreach($faculty as $fac)
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    <!-- Name & Avatar -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs uppercase shadow-xs
                                                @if($fac->isApprovalFaculty()) bg-emerald-100 text-emerald-700
                                                @elseif($fac->isNocAuthority()) bg-rose-100 text-rose-700
                                                @else bg-indigo-100 text-indigo-700
                                                @endif">
                                                {{ substr($fac->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $fac->name }}</p>
                                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">{{ $fac->role ? $fac->role->display_name : 'No Role' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Email & Phone -->
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        <div class="space-y-0.5">
                                            <p class="font-medium text-xs">{{ $fac->email }}</p>
                                            <p class="text-[10px] text-gray-400 font-mono">{{ $fac->phone ?? 'N/A' }}</p>
                                        </div>
                                    </td>

                                    <!-- Designation -->
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        <div class="space-y-0.5">
                                            <p class="font-semibold text-xs text-gray-700">{{ $fac->designation ?? 'Faculty Member' }}</p>
                                            <p class="text-[10px] text-gray-400">Department: {{ $fac->department ?? 'IT' }}</p>
                                        </div>
                                    </td>

                                    <!-- Statistics -->
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                        <div class="flex space-x-4 text-xs font-semibold">
                                            <span class="inline-flex items-center text-gray-600">
                                                <i class="fas fa-user-graduate text-gray-400 mr-1.5"></i>
                                                {{ $fac->students_count }} Students
                                            </span>
                                            <span class="inline-flex items-center text-gray-600">
                                                <i class="fas fa-graduation-cap text-gray-400 mr-1.5"></i>
                                                {{ $fac->batches_count }} Batches
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Current Authority Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            @if($fac->hasPermission('guide'))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                                    <i class="fas fa-user-tie mr-1 text-[10px]"></i>
                                                    Guide
                                                </span>
                                            @endif
                                            @if($fac->hasPermission('approval_faculty'))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <i class="fas fa-check-circle mr-1 text-[10px]"></i>
                                                    Approval
                                                </span>
                                            @endif
                                            @if($fac->hasPermission('noc_authority'))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                                    <i class="fas fa-stamp mr-1 text-[10px]"></i>
                                                    NOC
                                                </span>
                                            @endif
                                            @if($fac->permissions->isEmpty())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                                    No Permissions
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Actions Inline Form -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                        <div class="inline-flex items-center gap-2">
                                            <form action="{{ route('admin.faculty.update-authority', $fac->id) }}" method="POST" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg p-1.5 text-xs text-gray-700 font-medium">
                                                    <label class="flex items-center gap-1 cursor-pointer">
                                                        <input type="checkbox" name="permissions[]" value="guide" {{ $fac->hasPermission('guide') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        Guide
                                                    </label>
                                                    <label class="flex items-center gap-1 cursor-pointer">
                                                        <input type="checkbox" name="permissions[]" value="approval_faculty" {{ $fac->hasPermission('approval_faculty') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        Approval
                                                    </label>
                                                    <label class="flex items-center gap-1 cursor-pointer">
                                                        <input type="checkbox" name="permissions[]" value="noc_authority" {{ $fac->hasPermission('noc_authority') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        NOC
                                                    </label>
                                                </div>
                                                <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-xs transition duration-150">
                                                    Update
                                                </button>
                                            </form>
                                            <button type="button" @click="openDeleteFaculty(@json($fac))" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold shadow-xs transition duration-150" title="Delete Faculty">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- MODALS / POPUPS (Alpine controlled) -->
    <!-- ============================================== -->

    <!-- 1. BATCH MODAL (Create/Edit) -->
    <div x-show="showBatchModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showBatchModal = false" class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold" x-text="modalMode === 'create' ? 'Create Academic Batch' : 'Edit Batch Name'"></h3>
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Batch Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            x-model="batchForm.name" 
                            required 
                            placeholder="e.g. IT_2023, CE_2024" 
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <span class="text-xs text-gray-400 mt-1 block">Ensure this batch name is unique and represents the academic cohort.</span>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showBatchModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm" x-text="modalMode === 'create' ? 'Create' : 'Update'">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. STUDENT MODAL (Create/Edit) -->
    <div x-show="showStudentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showStudentModal = false" class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold" x-text="modalMode === 'create' ? 'Add Student Record' : 'Edit Student Record'"></h3>
                    <button @click="showStudentModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form :action="modalMode === 'create' ? '{{ route('admin.students.store') }}' : '{{ url('admin/students') }}/' + studentForm.id" method="POST" class="p-6 space-y-4">
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
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Assigned Guide / Faculty</label>
                        <select name="guide_id" x-model="studentForm.guide_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">-- Unassigned --</option>
                            @foreach($faculty as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }} ({{ $fac->department }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="modalMode === 'create'">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" x-model="studentForm.password" placeholder="Leave empty for default (password123)" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showStudentModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm" x-text="modalMode === 'create' ? 'Save' : 'Update'">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. IMPORT MODAL (Excel Upload - Students Only) -->
    <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showImportModal = false" class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold">
                        Import Students List
                    </h3>
                    <button @click="showImportModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Excel (.xlsx) file</label>
                        <input 
                            type="file" 
                            name="file" 
                            required 
                            accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" 
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-gray-50">
                    </div>

                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-xs space-y-2 text-indigo-900 leading-relaxed">
                        <p class="font-bold flex items-center">
                            <i class="fas fa-info-circle mr-1 text-indigo-600"></i>
                            Column Header Templates & Guidelines:
                        </p>
                        <div>
                            <p>Required columns: <code class="font-bold font-mono">Name</code>, <code class="font-bold font-mono">Email</code></p>
                            <p class="mt-1">Optional columns: <code class="font-bold font-mono">Enrollment Number</code>, <code class="font-bold font-mono">Department</code>, <code class="font-bold font-mono">Semester</code>, <code class="font-bold font-mono">Batch</code>, <code class="font-bold font-mono">Assigned Guide</code></p>
                        </div>
                        <p class="text-gray-500 font-medium mt-2">
                            <i class="fas fa-info-circle mr-0.5 text-indigo-500"></i>
                            Only Excel (.xlsx) files are accepted. CSV and other formats are not supported.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm">
                            Import List
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. VIEW BATCH DRILLDOWN DETAILS MODAL (Enhanced) -->
    <div x-show="showViewBatchStudentsModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showViewBatchStudentsModal = false" class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full" x-transition>
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold">
                        Drilldown Analysis: Batch <span x-text="viewBatch.name"></span>
                    </h3>
                    <button @click="showViewBatchStudentsModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Statistics aggregates within the batch -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 rounded-xl p-4 border border-gray-150">
                        <div class="p-2.5 text-center">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Total Students</p>
                            <p class="text-xl font-black text-gray-800 mt-1" x-text="viewBatch.students_count"></p>
                        </div>
                        <div class="p-2.5 text-center">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Pending Review</p>
                            <p class="text-xl font-black text-yellow-600 mt-1" x-text="viewBatch.pending_apps"></p>
                        </div>
                        <div class="p-2.5 text-center">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Approved Apps</p>
                            <p class="text-xl font-black text-emerald-600 mt-1" x-text="viewBatch.approved_apps"></p>
                        </div>
                        <div class="p-2.5 text-center">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">NOCs Generated</p>
                            <p class="text-xl font-black text-teal-600 mt-1" x-text="viewBatch.noc_count"></p>
                        </div>
                    </div>

                    <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Guide</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">App Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white text-xs">
                                <template x-if="viewBatch.students.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic font-semibold">No students are currently linked to this batch.</td>
                                    </tr>
                                </template>
                                <template x-for="student in viewBatch.students">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-700" x-text="student.enrollment_number || 'N/A'"></td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900" x-text="student.name"></div>
                                            <div class="text-[10px] text-gray-400 font-medium" x-text="student.email"></div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 font-semibold" x-text="student.guide_name"></td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span 
                                                class="px-2 py-0.5 rounded-full font-bold uppercase text-[9px]"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800': student.app_status === 'pending' || student.app_status === 'pending_higher',
                                                    'bg-blue-100 text-blue-800': student.app_status === 'faculty_approved',
                                                    'bg-green-100 text-green-800': student.app_status === 'higher_faculty_approved' || student.app_status === 'noc_generated',
                                                    'bg-red-100 text-red-800': student.app_status === 'faculty_rejected' || student.app_status === 'higher_faculty_rejected',
                                                    'bg-gray-100 text-gray-500': student.app_status === 'no_application'
                                                }"
                                                x-text="student.app_status.replace('_', ' ')">
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="showViewBatchStudentsModal = false" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition shadow-sm">
                            Close View
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. STUDENT DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteStudentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showDeleteStudentModal = false" class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <!-- Red warning header -->
                <div class="bg-red-600 px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-exclamation-triangle text-xl animate-pulse"></i>
                        <h3 class="text-lg font-bold">WARNING</h3>
                    </div>
                    <button type="button" @click="showDeleteStudentModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/students') }}/' + deleteStudentData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('DELETE')

                    <p class="text-sm font-semibold text-gray-800">
                        You are about to permanently delete this student record.
                    </p>

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
                        <i class="fas fa-info-circle mr-1 text-red-600"></i>
                        This action cannot be undone.
                    </p>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">
                            To confirm deletion, type the Enrollment Number exactly as shown:
                        </label>
                        <input 
                            type="text" 
                            name="confirmation_text" 
                            x-model="deleteStudentConfirmationInput" 
                            required 
                            placeholder="Type Enrollment Number" 
                            class="w-full border border-gray-300 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none font-mono">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showDeleteStudentModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="deleteStudentConfirmationInput !== deleteStudentData.enrollment_number"
                            :class="deleteStudentConfirmationInput !== deleteStudentData.enrollment_number 
                                ? 'opacity-50 cursor-not-allowed bg-red-400' 
                                : 'bg-red-600 hover:bg-red-700 text-white font-bold shadow-sm'" 
                            class="px-5 py-2 rounded-lg text-sm font-bold transition">
                            Delete Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 6. FACULTY DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteFacultyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="showDeleteFacultyModal = false" class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full" x-transition>
                <!-- Red warning header -->
                <div class="bg-red-600 px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-exclamation-triangle text-xl animate-pulse"></i>
                        <h3 class="text-lg font-bold">WARNING</h3>
                    </div>
                    <button type="button" @click="showDeleteFacultyModal = false" class="text-white/80 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <form :action="'{{ url('admin/faculty') }}/' + deleteFacultyData.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('DELETE')

                    <p class="text-sm font-semibold text-gray-800">
                        You are about to permanently delete this faculty record.
                    </p>

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
                        <i class="fas fa-info-circle mr-1 text-red-600"></i>
                        This action cannot be undone.
                    </p>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">
                            To confirm deletion, type the faculty full name exactly as shown:
                        </label>
                        <input 
                            type="text" 
                            name="confirmation_text" 
                            x-model="deleteFacultyConfirmationInput" 
                            required 
                            placeholder="Type Faculty Full Name" 
                            class="w-full border border-gray-300 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" @click="showDeleteFacultyModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="deleteFacultyConfirmationInput !== deleteFacultyData.name"
                            :class="deleteFacultyConfirmationInput !== deleteFacultyData.name 
                                ? 'opacity-50 cursor-not-allowed bg-red-400' 
                                : 'bg-red-600 hover:bg-red-700 text-white font-bold shadow-sm'" 
                            class="px-5 py-2 rounded-lg text-sm font-bold transition">
                            Delete Faculty
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
