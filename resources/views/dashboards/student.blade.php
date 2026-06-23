@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    @include('layouts.navigation')

    <div class="mt-8"></div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Applications -->
            <div class="bg-blue-500 rounded-xl shadow-lg shadow-blue-500 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-1">Total Applications</p>
                        <h3 class="text-3xl font-bold">{{ $applications->count() }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-100">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>All time submissions</span>
                </div>
            </div>

            <!-- Pending Review -->
            <div class="bg-orange-500 rounded-xl shadow-lg shadow-orange-500 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium mb-1">Pending Review</p>
                        <h3 class="text-3xl font-bold">{{ $applications->where('status', 'pending')->count() }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-orange-100">
                    <i class="fas fa-hourglass-half mr-1"></i>
                    <span>Awaiting approval</span>
                </div>
            </div>

            <!-- NOC Generated -->
            <div class="bg-green-500 rounded-xl shadow-lg shadow-green-500 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium mb-1">NOC Generated</p>
                        <h3 class="text-3xl font-bold">{{ $applications->where('status', 'noc_generated')->count() }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-green-100">
                    <i class="fas fa-download mr-1"></i>
                    <span>Ready to download</span>
                </div>
            </div>

            <!-- Rejected -->
            <div class="bg-red-500 rounded-xl shadow-lg shadow-red-500 p-4 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium mb-1">Rejected</p>
                        <h3 class="text-3xl font-bold">{{ $applications->whereIn('status', ['faculty_rejected', 'higher_faculty_rejected'])->count() }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <i class="fas fa-times text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-red-100">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <span>Need revision</span>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="mb-6 flex gap-3 flex-wrap">
            <a href="{{ route('student.applications.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition shadow-lg shadow-blue-500">
                <i class="fas fa-plus-circle mr-2"></i>
                Submit New Application
            </a>
            
            @php
                $latestApp = $applications->sortByDesc('created_at')->first();
                $canRequestNoc = $latestApp && $latestApp->status === 'faculty_approved' && !$latestApp->noc_requested;
                $hasNocRequested = $latestApp && ($latestApp->noc_requested || in_array($latestApp->status, ['pending_higher', 'higher_faculty_approved', 'noc_generated']));
            @endphp
            
            @if($canRequestNoc)
                <button onclick="requestNoc()" class="inline-flex items-center px-4 py-2 bg-purple-500 text-white text-sm font-medium rounded-lg hover:bg-purple-600 transition shadow-lg shadow-purple-500">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Request NOC
                </button>
            @elseif($hasNocRequested)
                <button disabled class="inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg cursor-not-allowed shadow-sm">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Request NOC
                </button>
            @else
                <button disabled class="inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg cursor-not-allowed shadow-sm">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Request NOC
                </button>
            @endif
        </div>
        
        <!-- Alerts -->
        @if($hasNocRequested)
            <div class="mb-6 p-4 bg-yellow-100 border border-yellow-200 rounded-md">
                <p class="text-yellow-800 text-sm flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    You have already requested or generated a NOC for your latest application. You cannot request it again.
                </p>
            </div>
        @elseif(!$canRequestNoc)
            <div class="mb-6 p-4 bg-yellow-100 border border-yellow-200 rounded-md">
                <p class="text-yellow-800 text-sm flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    You don't have any approved applications. Once your latest application is approved by faculty, you can request a NOC.
                </p>
            </div>
        @endif

        <!-- Applications List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-600 p-4">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-list-alt mr-2"></i>
                    My Applications
                </h3>
            </div>
            
            <div>
                @if($applications->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500">No applications found.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($applications as $application)
                                    <tr class="hover:bg-gray-50">
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
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock'],
                                                    'faculty_approved' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-check'],
                                                    'faculty_rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times'],
                                                    'higher_faculty_approved' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'icon' => 'fa-check-double'],
                                                    'higher_faculty_rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times'],
                                                    'noc_generated' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-circle'],
                                                ];
                                                $config = $statusConfig[$application->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-info-circle'];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                                <i class="fas {{ $config['icon'] }} mr-1.5 text-[10px]"></i>
                                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                                {{ $application->created_at->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('student.applications.show', $application) }}" class="inline-flex items-center px-3 py-1 border border-blue-200 text-blue-600 rounded hover:bg-blue-50 transition">
                                                    <i class="fas fa-eye mr-1.5 text-xs"></i>
                                                    View
                                                </a>
                                                @if($application->status === 'noc_generated')
                                                    <a href="{{ route('student.applications.download-noc', $application) }}" class="inline-flex items-center px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition">
                                                        <i class="fas fa-download mr-1.5 text-xs"></i>
                                                        NOC
                                                    </a>
                                                @endif
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

<script>
    function requestNoc() {
        if (confirm('Are you sure you want to request NOC? This will forward your application to the higher faculty for review.')) {
            @if($canRequestNoc)
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("student.applications.request-noc", $latestApp) }}';
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
                document.body.appendChild(form);
                form.submit();
            @endif
        }
    }
</script>
@endsection