@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    @include('layouts.navigation')

    <div class="mt-8"></div>

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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
            <!-- Total Students -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-cyan-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-graduate text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-cyan-600 text-xs font-bold uppercase tracking-wider">Total Students</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalStudents }}</h3>
                    </div>
                </div>
            </div>

            <!-- Total Batches -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-blue-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-layer-group text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-blue-600 text-xs font-bold uppercase tracking-wider">Batches</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalBatches }}</h3>
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

            <!-- Pending -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-rose-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-rose-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-rose-600 text-xs font-bold uppercase tracking-wider">Pending Review</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $pendingApplications }}</h3>
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
                        <p class="text-green-600 text-xs font-bold uppercase tracking-wider">Approved</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $approvedApplications }}</h3>
                    </div>
                </div>
            </div>

            <!-- NOC Generated -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-t-[3px] border-t-pink-400 p-5 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-11 h-11 rounded-xl bg-pink-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-certificate text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-pink-600 text-xs font-bold uppercase tracking-wider">NOCs Issued</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $nocGenerated }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Cards Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-indigo-600 p-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    My Assigned Batches
                </h3>
                <span class="bg-indigo-700 text-white text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    {{ $totalBatches }} {{ Str::plural('Batch', $totalBatches) }}
                </span>
            </div>

            @if($batches->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-500 font-medium">No batches assigned to you yet.</p>
                    <p class="text-gray-400 text-sm mt-1">Students will appear here once they are assigned to you.</p>
                </div>
            @else
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($batches as $batch)
                            @php
                                $studentCount = $batchStudentCounts[$batch->id] ?? 0;
                                // Cycle through accent colors for visual variety
                                $colors = [
                                    ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'icon' => 'text-indigo-600', 'badge' => 'bg-indigo-100 text-indigo-700', 'hover' => 'hover:border-indigo-400 hover:shadow-indigo-100'],
                                    ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-700', 'hover' => 'hover:border-blue-400 hover:shadow-blue-100'],
                                    ['bg' => 'bg-teal-50', 'border' => 'border-teal-200', 'icon' => 'text-teal-600', 'badge' => 'bg-teal-100 text-teal-700', 'hover' => 'hover:border-teal-400 hover:shadow-teal-100'],
                                    ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => 'text-purple-600', 'badge' => 'bg-purple-100 text-purple-700', 'hover' => 'hover:border-purple-400 hover:shadow-purple-100'],
                                    ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'icon' => 'text-orange-600', 'badge' => 'bg-orange-100 text-orange-700', 'hover' => 'hover:border-orange-400 hover:shadow-orange-100'],
                                    ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'icon' => 'text-rose-600', 'badge' => 'bg-rose-100 text-rose-700', 'hover' => 'hover:border-rose-400 hover:shadow-rose-100'],
                                ];
                                $color = $colors[$loop->index % count($colors)];
                            @endphp
                            <a href="{{ route('faculty.guide.batch-students', $batch) }}"
                               class="group block {{ $color['bg'] }} border {{ $color['border'] }} rounded-xl p-5 transition-all duration-300 {{ $color['hover'] }} hover:shadow-lg hover:scale-[1.02]">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-lg {{ $color['badge'] }} flex items-center justify-center">
                                            <i class="fas fa-users text-base"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">{{ $batch->name }}</h4>
                                            <p class="text-xs text-gray-500 mt-0.5">Click to view students</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all duration-300 text-sm mt-2"></i>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $color['badge'] }}">
                                        <i class="fas fa-user-graduate mr-1.5 text-[10px]"></i>{{ $studentCount }} {{ Str::plural('Student', $studentCount) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
