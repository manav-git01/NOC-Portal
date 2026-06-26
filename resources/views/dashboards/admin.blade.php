@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    @include('layouts.navigation')

    <div class="mt-8"></div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-cyan-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-cyan-600 text-xs font-bold uppercase tracking-wider">Total Users</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalUsers }}</h3>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-blue-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-graduate text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-blue-600 text-xs font-bold uppercase tracking-wider">Total Students</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalStudents }}</h3>
                    </div>
                </div>
            </div>

            <!-- Total Faculty -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-teal-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-teal-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chalkboard-teacher text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-teal-600 text-xs font-bold uppercase tracking-wider">Total Faculty</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalFaculty }}</h3>
                    </div>
                </div>
            </div>

            <!-- Higher Faculty -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-purple-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-purple-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-purple-600 text-xs font-bold uppercase tracking-wider">Higher Faculty</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalHigherFaculty }}</h3>
                    </div>
                </div>
            </div>

            <!-- Total Applications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-orange-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-signature text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-orange-600 text-xs font-bold uppercase tracking-wider">Applications</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalApplications }}</h3>
                    </div>
                </div>
            </div>

            <!-- Approved Applications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-green-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-green-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-green-600 text-xs font-bold uppercase tracking-wider">Approved</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $approvedApplications }}</h3>
                    </div>
                </div>
            </div>

            <!-- Rejected Applications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-rose-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-rose-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-times-circle text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-rose-600 text-xs font-bold uppercase tracking-wider">Rejected</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $rejectedApplications }}</h3>
                    </div>
                </div>
            </div>

            <!-- Generated NOCs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-pink-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-pink-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-certificate text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-pink-600 text-xs font-bold uppercase tracking-wider">NOCs Generated</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $generatedNocs }}</h3>
                    </div>
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
