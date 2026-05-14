@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Left Side - User Info -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-chalkboard-teacher text-white"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Faculty - {{ auth()->user()->department }}</p>
                    </div>
                </div>

                <!-- Right Side - Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center space-x-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition font-medium text-sm">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Pending Review -->
            <div class="bg-blue-500 rounded-xl shadow-lg shadow-blue-500 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-1">Pending Review</p>
                        <h3 class="text-3xl font-bold">{{ $pendingApplications->count() }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-100">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>Awaiting your review</span>
                </div>
            </div>

            <!-- Approved by You -->
            <div class="bg-green-500 rounded-xl shadow-lg shadow-green-500 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium mb-1">Approved by You</p>
                        <h3 class="text-3xl font-bold">{{ $approvedCount ?? 0 }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-green-100">
                    <i class="fas fa-check mr-1"></i>
                    <span>Total approvals</span>
                </div>
            </div>

            <!-- Rejected by You -->
            <div class="bg-red-500 rounded-xl shadow-lg shadow-red-500 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium mb-1">Rejected by You</p>
                        <h3 class="text-3xl font-bold">{{ $rejectedCount ?? 0 }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-times-circle text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-red-100">
                    <i class="fas fa-ban mr-1"></i>
                    <span>Total rejections</span>
                </div>
            </div>

            <!-- Total Reviewed -->
            <div class="bg-green-600 rounded-xl shadow-lg shadow-green-600 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-green-200 text-sm font-medium mb-1">Total Reviewed</p>
                        <h3 class="text-3xl font-bold">{{ $totalReviewed ?? 0 }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-clipboard-check text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-green-200">
                    <i class="fas fa-list mr-1"></i>
                    <span>All decisions made</span>
                </div>
            </div>
        </div>

        <!-- Pending Applications Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-600 p-4">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-file-signature mr-2"></i>
                    Applications for Review
                </h3>
            </div>
            
            <div>
                @if($pendingApplications->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500">No applications pending your review.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($pendingApplications as $application)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="bg-gray-100 text-gray-500 rounded p-2 mr-3">
                                                    <i class="fas fa-user-graduate"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">{{ $application->user->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $application->user->enrollment_number }}<br>{{ $application->user->department }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="bg-blue-50 text-blue-500 rounded p-2 mr-3">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">{{ $application->company_name }}</div>
                                                    <div class="text-xs text-gray-500">{{ Str::limit($application->company_address, 25) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 flex items-center">
                                                <i class="fas fa-calendar text-blue-500 mr-2 text-xs"></i>
                                                {{ $application->start_date->format('M d, Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1 flex items-center">
                                                <i class="fas fa-arrow-right text-gray-400 mr-2"></i>
                                                {{ $application->end_date->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1.5 text-[10px]"></i>
                                                Pending Review
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('faculty.applications.show', $application) }}" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                                    <i class="fas fa-check mr-1.5 text-xs"></i>
                                                    Review
                                                </a>
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
    </div>
</div>
@endsection