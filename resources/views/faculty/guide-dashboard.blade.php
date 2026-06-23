@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    @include('layouts.navigation')

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Error & Success Messages -->
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-lg shadow-sm flex items-center">
                <i class="fas fa-check-circle mr-3 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Page Header & Stat Card -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-graduate text-indigo-600 mr-3"></i>My Assigned Students
                </h1>
                <p class="text-sm text-gray-500 mt-1">Monitor your students' internship applications and NOC status</p>
            </div>
            <div class="bg-indigo-600 rounded-xl shadow-lg shadow-indigo-600/20 px-6 py-4 text-white flex items-center space-x-4">
                <div class="bg-white/20 rounded-lg p-2.5">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-indigo-100 text-xs font-medium">Total Assigned</p>
                    <h3 class="text-2xl font-bold">{{ $totalStudents }}</h3>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('faculty.guide-dashboard') }}" class="flex flex-col md:flex-row md:items-end gap-4" id="filterForm">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search Student</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Name, Enrollment No., or Email..."
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Application Status Filter -->
                <div class="w-full md:w-52">
                    <label for="status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Application Status</label>
                    <select name="status" id="status"
                            class="block w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="not_applied" {{ request('status') === 'not_applied' ? 'selected' : '' }}>Not Applied</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Submitted</option>
                        <option value="faculty_approved" {{ request('status') === 'faculty_approved' ? 'selected' : '' }}>Faculty Approved</option>
                        <option value="pending_higher" {{ request('status') === 'pending_higher' ? 'selected' : '' }}>Pending Higher Review</option>
                        <option value="higher_faculty_approved" {{ request('status') === 'higher_faculty_approved' ? 'selected' : '' }}>Approved</option>
                        <option value="noc_generated" {{ request('status') === 'noc_generated' ? 'selected' : '' }}>NOC Generated</option>
                        <option value="faculty_rejected" {{ request('status') === 'faculty_rejected' ? 'selected' : '' }}>Rejected by Faculty</option>
                        <option value="higher_faculty_rejected" {{ request('status') === 'higher_faculty_rejected' ? 'selected' : '' }}>Rejected by Higher Faculty</option>
                    </select>
                </div>

                <!-- NOC Status Filter -->
                <div class="w-full md:w-48">
                    <label for="noc_status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">NOC Status</label>
                    <select name="noc_status" id="noc_status"
                            class="block w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ request('noc_status') === 'all' || !request('noc_status') ? 'selected' : '' }}>All</option>
                        <option value="not_requested" {{ request('noc_status') === 'not_requested' ? 'selected' : '' }}>Not Requested</option>
                        <option value="requested" {{ request('noc_status') === 'requested' ? 'selected' : '' }}>Requested</option>
                        <option value="generated" {{ request('noc_status') === 'generated' ? 'selected' : '' }}>Generated</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                        <i class="fas fa-filter mr-1.5 text-xs"></i>Filter
                    </button>
                    <a href="{{ route('faculty.guide-dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
                        <i class="fas fa-times mr-1.5 text-xs"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Student Directory Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-id-card-alt mr-3"></i>Student Directory
                </h3>
                <span class="text-indigo-100 text-sm font-medium">
                    Showing {{ $students->count() }} {{ Str::plural('student', $students->count()) }}
                </span>
            </div>

            @if($students->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-user-slash text-5xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-500 font-medium">No students found matching your criteria.</p>
                    @if(request()->hasAny(['search', 'status', 'noc_status']))
                        <a href="{{ route('faculty.guide-dashboard') }}" class="inline-flex items-center mt-3 text-indigo-600 hover:text-indigo-800 text-sm font-semibold">
                            <i class="fas fa-arrow-left mr-1.5"></i>Clear filters and show all students
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment No.</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Semester</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Internship Company</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Application Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NOC Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Updated</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($students as $student)
                                @php
                                    $latestApp = $student->internshipApplications->first();
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <!-- Enrollment Number -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ $student->enrollment_number ?? 'N/A' }}
                                    </td>

                                    <!-- Student Name -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs mr-3 flex-shrink-0">
                                                {{ strtoupper(substr($student->name, 0, 2)) }}
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ $student->name }}</span>
                                        </div>
                                    </td>

                                    <!-- Email -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $student->email }}
                                    </td>

                                    <!-- Semester -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                        {{ $student->semester ?? '—' }}
                                    </td>

                                    <!-- Internship Company -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                        @if($latestApp && $latestApp->company_name)
                                            {{ Str::limit($latestApp->company_name, 25) }}
                                        @else
                                            <span class="text-gray-400 italic">—</span>
                                        @endif
                                    </td>

                                    <!-- Duration -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @if($latestApp && $latestApp->start_date && $latestApp->end_date)
                                            {{ $latestApp->start_date->format('d M') }} – {{ $latestApp->end_date->format('d M, Y') }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    <!-- Application Status Badge -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        @if(!$latestApp)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                <i class="fas fa-minus-circle mr-1 text-[10px]"></i>Not Applied
                                            </span>
                                        @elseif($latestApp->status === 'draft')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                <i class="fas fa-pencil-alt mr-1 text-[10px]"></i>Draft
                                            </span>
                                        @elseif($latestApp->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1 text-[10px]"></i>Submitted
                                            </span>
                                        @elseif($latestApp->status === 'faculty_approved')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                <i class="fas fa-check-circle mr-1 text-[10px]"></i>Faculty Approved
                                            </span>
                                        @elseif($latestApp->status === 'pending_higher')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                <i class="fas fa-hourglass-half mr-1 text-[10px]"></i>Pending Higher Review
                                            </span>
                                        @elseif($latestApp->status === 'higher_faculty_approved')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <i class="fas fa-check-double mr-1 text-[10px]"></i>Approved
                                            </span>
                                        @elseif($latestApp->status === 'noc_generated')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                                                <i class="fas fa-certificate mr-1 text-[10px]"></i>NOC Generated
                                            </span>
                                        @elseif($latestApp->status === 'faculty_rejected')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1 text-[10px]"></i>Rejected by Faculty
                                            </span>
                                        @elseif($latestApp->status === 'higher_faculty_rejected')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1 text-[10px]"></i>Rejected by Higher Faculty
                                            </span>
                                        @endif
                                    </td>

                                    <!-- NOC Status Badge -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        @if($latestApp && $latestApp->noc)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                                                <i class="fas fa-file-contract mr-1 text-[10px]"></i>Generated
                                            </span>
                                        @elseif($latestApp && $latestApp->noc_requested)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-paper-plane mr-1 text-[10px]"></i>Requested
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                                <i class="fas fa-minus mr-1 text-[10px]"></i>Not Requested
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Last Updated -->
                                    <td class="px-4 py-4 whitespace-nowrap text-xs text-gray-500">
                                        @if($latestApp)
                                            {{ $latestApp->updated_at->format('d M, Y') }}
                                            <br>
                                            <span class="text-gray-400">{{ $latestApp->updated_at->format('h:i A') }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        @if($latestApp)
                                            <a href="{{ route('faculty.guide.application-details', $latestApp) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition shadow-sm">
                                                <i class="fas fa-eye mr-1.5 text-[10px]"></i>View Details
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No application</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
