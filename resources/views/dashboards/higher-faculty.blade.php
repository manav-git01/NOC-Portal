@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    @include('layouts.navigation')

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
            <!-- Awaiting Final Approval -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-blue-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clipboard-check text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-blue-600 text-xs font-bold uppercase tracking-wider">Awaiting Final Approval</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $pendingApplications->count() }}</h3>
                    </div>
                </div>
            </div>

            <!-- NOCs Generated -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-green-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-green-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-certificate text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-green-600 text-xs font-bold uppercase tracking-wider">NOCs Generated</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $approvedCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Rejected by You -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-rose-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-rose-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-times-circle text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-rose-600 text-xs font-bold uppercase tracking-wider">Rejected by You</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $rejectedCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <!-- Total Reviewed -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-teal-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-teal-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clipboard-check text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-teal-600 text-xs font-bold uppercase tracking-wider">Total Reviewed</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalReviewed ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Faculty Approved Applications Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-green-700 p-4">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-file-signature mr-2"></i>
                    Applications Awaiting Final Approval
                </h3>
            </div>
            
            <div>
                @if($pendingApplications->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500">No applications awaiting final approval at the moment.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NOC Requested</th>
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
                                                    <div class="text-xs text-gray-500">{{ $application->user->enrollment_number }}<br>{{ $application->user->department }} - Sem {{ $application->user->semester }}</div>
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
                                            @if($application->noc_requested)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-file-invoice mr-1.5 text-[10px]"></i>
                                                    Yes
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-times-circle mr-1.5 text-[10px]"></i>
                                                    No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('higher-faculty.applications.show', $application) }}" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                                    <i class="fas fa-certificate mr-1.5 text-xs"></i>
                                                    Review & Generate NOC
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