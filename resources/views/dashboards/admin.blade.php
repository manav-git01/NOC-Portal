@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    @include('layouts.navigation')

    <div class="mt-8"></div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Users -->
            <div class="bg-indigo-500 rounded-xl shadow-lg shadow-indigo-500/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-indigo-100 text-sm font-medium mb-1">Total Users</p>
                        <h3 class="text-3xl font-bold">{{ $totalUsers }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-indigo-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Total registered users</span>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-blue-500 rounded-xl shadow-lg shadow-blue-500/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-1">Total Students</p>
                        <h3 class="text-3xl font-bold">{{ $totalStudents }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Registered in portal</span>
                </div>
            </div>

            <!-- Total Faculty -->
            <div class="bg-teal-500 rounded-xl shadow-lg shadow-teal-500/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-teal-100 text-sm font-medium mb-1">Total Faculty</p>
                        <h3 class="text-3xl font-bold">{{ $totalFaculty }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-teal-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Application reviewers</span>
                </div>
            </div>

            <!-- Total Higher Faculty -->
            <div class="bg-purple-500 rounded-xl shadow-lg shadow-purple-500/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium mb-1">Higher Faculty</p>
                        <h3 class="text-3xl font-bold">{{ $totalHigherFaculty }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-graduation-cap text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-purple-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Final approvers / NOC issuers</span>
                </div>
            </div>

            <!-- Total Applications -->
            <div class="bg-orange-500 rounded-xl shadow-lg shadow-orange-500/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium mb-1">Total Applications</p>
                        <h3 class="text-3xl font-bold">{{ $totalApplications }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-file-signature text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-orange-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Submitted by students</span>
                </div>
            </div>

            <!-- Approved Applications -->
            <div class="bg-green-500 rounded-xl shadow-lg shadow-green-500/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium mb-1">Approved Applications</p>
                        <h3 class="text-3xl font-bold">{{ $approvedApplications }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-green-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Approved by Faculty/Higher</span>
                </div>
            </div>

            <!-- Rejected Applications -->
            <div class="bg-red-500 rounded-xl shadow-lg shadow-red-500/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium mb-1">Rejected Applications</p>
                        <h3 class="text-3xl font-bold">{{ $rejectedApplications }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-times-circle text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-red-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Rejected applications</span>
                </div>
            </div>

            <!-- Generated NOCs -->
            <div class="bg-emerald-600 rounded-xl shadow-lg shadow-emerald-600/20 p-4 text-white hover:scale-[1.02] transition-transform duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm font-medium mb-1">Generated NOCs</p>
                        <h3 class="text-3xl font-bold">{{ $generatedNocs }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-certificate text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-emerald-100">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <span>Official NOC PDFs issued</span>
                </div>
            </div>
        </div>

        <!-- Recent Applications Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="bg-indigo-600 p-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-file-alt mr-2"></i>
                    Recent Internship Applications
                </h3>
                <span class="bg-indigo-700 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    Latest 10
                </span>
            </div>
            
            <div>
                @if($recentApplications->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500">No applications submitted yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role / Tech</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted At</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($recentApplications as $application)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900">{{ $application->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $application->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900">{{ $application->company_name }}</div>
                                            @if($application->company_website)
                                                <div class="text-xs text-indigo-600 hover:underline">
                                                    <a href="{{ $application->company_website }}" target="_blank">{{ $application->company_website }}</a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $application->internship_position }}</div>
                                            <div class="text-xs text-gray-500">{{ $application->technology }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClasses = [
                                                    'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pending Faculty'],
                                                    'faculty_approved' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Faculty Approved'],
                                                    'faculty_rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Faculty Rejected'],
                                                    'pending_higher' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Pending Higher Faculty'],
                                                    'higher_faculty_approved' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Approved (Pending NOC)'],
                                                    'higher_faculty_rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Higher Faculty Rejected'],
                                                    'noc_generated' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'NOC Generated']
                                                ];
                                                $currentStatus = $statusClasses[$application->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => $application->status];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $currentStatus['bg'] }} {{ $currentStatus['text'] }}">
                                                {{ $currentStatus['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $application->created_at ? $application->created_at->format('M d, Y H:i') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Users Management Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-indigo-600 p-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-users mr-2"></i>
                    User Directory & Roles
                </h3>
                <span class="bg-indigo-700 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    Total Users: {{ $users->count() }}
                </span>
            </div>
            
            <div>
                @if($users->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500">No users registered in the system.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User Info</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department / Semester</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Registered At</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($users as $user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                                    @if($user->phone)
                                                        <div class="text-xs text-gray-500">{{ $user->phone }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->isAdmin())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                    <i class="fas fa-user-shield mr-1 text-[10px]"></i>
                                                    Admin
                                                </span>
                                            @elseif($user->isHigherFaculty())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                                                    <i class="fas fa-graduation-cap mr-1 text-[10px]"></i>
                                                    Higher Faculty
                                                </span>
                                            @elseif($user->isFaculty())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800 border border-teal-200">
                                                    <i class="fas fa-chalkboard-teacher mr-1 text-[10px]"></i>
                                                    Faculty
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                    <i class="fas fa-user-graduate mr-1 text-[10px]"></i>
                                                    Student
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($user->department)
                                                <div class="text-sm font-medium text-gray-900">{{ $user->department }}</div>
                                            @else
                                                <div class="text-sm text-gray-400">N/A</div>
                                            @endif

                                            @if($user->semester)
                                                <div class="text-xs text-gray-500">Semester: {{ $user->semester }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->created_at->format('M d, Y H:i') }}
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
</div>
@endsection
