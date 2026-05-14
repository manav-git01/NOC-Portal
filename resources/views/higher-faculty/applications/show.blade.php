@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Left Side - User Info -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-shield text-white"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Higher Faculty - {{ auth()->user()->department }}</p>
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Back Button -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium transition text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mt-2 flex items-center">
                    <i class="fas fa-certificate text-green-600 mr-2"></i>
                    Final Review & NOC Generation
                </h1>
                <p class="text-gray-600 text-sm mt-1">Review faculty-approved application and generate NOC</p>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="mb-6">
            @if($application->status === 'pending_higher')
                <div class="bg-blue-600 rounded-xl shadow-sm p-4 text-white">
                    <div class="flex items-center">
                        <div class="bg-white/20 rounded-lg p-3 mr-4">
                            <i class="fas fa-file-invoice text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">NOC Requested</h3>
                            <p class="text-blue-100 text-sm mt-1">The student has requested a NOC for this faculty-approved application. Please review and generate the NOC if appropriate.</p>
                            <p class="text-xs text-blue-200 mt-2 flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                Requested {{ $application->higher_faculty_reviewed_at ? $application->higher_faculty_reviewed_at->diffForHumans() : 'recently' }}
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($application->status === 'faculty_approved')
                <div class="bg-blue-600 rounded-xl shadow-sm p-4 text-white">
                    <div class="flex items-center">
                        <div class="bg-white/20 rounded-lg p-3 mr-4">
                            <i class="fas fa-clipboard-check text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Faculty Approved</h3>
                            <p class="text-blue-100 text-sm mt-1">This application has been approved by faculty and is awaiting your final decision.</p>
                        </div>
                    </div>
                </div>
            @elseif($application->status === 'higher_faculty_approved' || $application->status === 'noc_generated')
                <div class="bg-green-600 rounded-xl shadow-sm p-4 text-white">
                    <div class="flex items-center">
                        <div class="bg-white/20 rounded-lg p-3 mr-4">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">NOC Generated</h3>
                            <p class="text-green-100 text-sm mt-1">No Objection Certificate has been successfully generated for this application.</p>
                        </div>
                    </div>
                </div>
            @elseif($application->status === 'higher_faculty_rejected')
                <div class="bg-red-600 rounded-xl shadow-sm p-4 text-white">
                    <div class="flex items-center">
                        <div class="bg-white/20 rounded-lg p-3 mr-4">
                            <i class="fas fa-times-circle text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Rejected by You</h3>
                            <p class="text-red-100 text-sm mt-1">You have rejected this application.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Content (Left Column - 2/3 width) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Student Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-blue-700 px-4 py-3">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-user-graduate mr-2"></i>
                            Student Information
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-user text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Full Name</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->user->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-envelope text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Email Address</p>
                                    <p class="text-gray-900 font-semibold text-sm break-all">{{ $application->user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-id-card text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Enrollment Number</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->user->enrollment_number }}</p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-phone text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Phone Number</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->user->phone }}</p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-building text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Department</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->user->department }}</p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-calendar-alt text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Semester</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->user->semester }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-teal-600 px-4 py-3">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-building mr-2"></i>
                            Company Information
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2 flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-briefcase text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Company Name</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->company_name }}</p>
                                </div>
                            </div>
                            <div class="md:col-span-2 flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-map-marker-alt text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Address</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->company_address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Internship Details -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-indigo-600 px-4 py-3">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-laptop-code mr-2"></i>
                            Internship Details
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2 flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-user-tie text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Position/Role</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->internship_position }}</p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-calendar-check text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Start Date</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->start_date->format('F d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-calendar-times text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">End Date</p>
                                    <p class="text-gray-900 font-semibold text-sm">{{ $application->end_date->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-orange-500 px-4 py-3">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-file-alt mr-2"></i>
                            Documents
                        </h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <!-- Company Letter -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center">
                                <div class="bg-gray-200 rounded p-2 mr-3">
                                    <i class="fas fa-file-pdf text-gray-500"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">Company Letter</p>
                                    <p class="text-xs text-gray-600">Offer/Acceptance Letter</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($application->company_letter_path) }}" target="_blank" 
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                                <i class="fas fa-eye mr-1.5"></i>
                                View
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Sidebar (1/3 width) -->
            <div class="space-y-6">
                
                <!-- Faculty Approval History -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-purple-600 px-4 py-3">
                        <h3 class="text-base font-bold text-white flex items-center">
                            <i class="fas fa-history mr-2"></i>
                            Approval History
                        </h3>
                    </div>
                    <div class="p-4">
                        @if($application->faculty_reviewed_at)
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg mb-3">
                                <div class="bg-green-100 rounded-full p-1.5 mr-3 mt-0.5">
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">Faculty Approved</p>
                                    <p class="text-xs text-gray-500">{{ $application->faculty_reviewed_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($application->higher_faculty_reviewed_at)
                            <div class="flex items-start bg-gray-50 p-3 rounded-lg">
                                <div class="bg-{{ $application->status === 'higher_faculty_rejected' ? 'red' : 'green' }}-100 rounded-full p-1.5 mr-3 mt-0.5">
                                    <i class="fas fa-{{ $application->status === 'higher_faculty_rejected' ? 'times' : 'check' }} text-{{ $application->status === 'higher_faculty_rejected' ? 'red' : 'green' }}-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">
                                        {{ $application->status === 'higher_faculty_rejected' ? 'Rejected by You' : 'Approved by You' }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $application->higher_faculty_reviewed_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-3 text-gray-500 bg-gray-50 rounded-lg text-sm flex items-center justify-center">
                                <i class="fas fa-hourglass-half mr-2"></i>
                                Awaiting your decision
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Decision Forms -->
                @if($application->status === 'faculty_approved' || $application->status === 'pending_higher')
                    <!-- Approve Form -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-green-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                Approve & Generate NOC
                            </h3>
                        </div>
                        <div class="p-4">
                            <form method="POST" action="{{ route('higher-faculty.applications.approve', $application) }}" id="approveForm">
                                @csrf
                                <div class="mb-4">
                                    <label for="approve_remarks" class="block text-xs font-medium text-gray-700 mb-1">
                                        <i class="fas fa-comment mr-1"></i>
                                        Remarks (Optional)
                                    </label>
                                    <textarea id="approve_remarks" name="remarks" rows="2" 
                                              class="w-full border-gray-300 rounded-md text-sm"
                                              placeholder="Add any comments..."></textarea>
                                </div>
                                <button type="submit" 
                                        onclick="return confirm('Are you sure you want to approve this application and generate NOC?')"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition font-medium text-sm"
                                        id="approveButton">
                                    <i class="fas fa-certificate mr-2"></i>
                                    Approve & Generate NOC
                                </button>
                            </form>
                        </div>
                    </div>

                    <script>
                        document.getElementById('approveForm').addEventListener('submit', function(e) {
                            const button = document.getElementById('approveButton');
                            button.disabled = true;
                            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                            button.classList.add('opacity-75', 'cursor-not-allowed');
                        });
                    </script>

                    <!-- Reject Form -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-red-600 px-4 py-3">
                            <h3 class="text-base font-bold text-white flex items-center">
                                <i class="fas fa-times-circle mr-2"></i>
                                Reject Application
                            </h3>
                        </div>
                        <div class="p-4">
                            <form method="POST" action="{{ route('higher-faculty.applications.reject', $application) }}" id="rejectForm">
                                @csrf
                                <div class="mb-4">
                                    <label for="reject_remarks" class="block text-xs font-medium text-gray-700 mb-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Reason for Rejection <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="reject_remarks" name="remarks" rows="2" required
                                              class="w-full border-gray-300 rounded-md text-sm"
                                              placeholder="Provide reason..."></textarea>
                                </div>
                                <button type="submit" 
                                        onclick="return confirm('Are you sure you want to REJECT this application? This action cannot be undone.')"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition font-medium text-sm">
                                    <i class="fas fa-ban mr-2"></i>
                                    Reject Application
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>
</div>
@endsection