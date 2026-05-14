@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-clipboard-check mr-2 text-indigo-600"></i>Review Application
        </h1>
        <p class="text-gray-600">Review and approve/reject student internship application</p>
    </div>

    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Student Information Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-user-graduate mr-3"></i>Student Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-user text-purple-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Full Name</p>
                            <p class="text-gray-800 font-semibold">{{ $application->user->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-envelope text-purple-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Email Address</p>
                            <p class="text-gray-800 font-semibold">{{ $application->user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-id-card text-purple-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Enrollment Number</p>
                            <p class="text-gray-800 font-semibold">{{ $application->user->enrollment_number }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-phone text-purple-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Phone Number</p>
                            <p class="text-gray-800 font-semibold">{{ $application->user->phone }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-building text-purple-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Department</p>
                            <p class="text-gray-800 font-semibold">{{ $application->user->department }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-graduation-cap text-purple-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Semester</p>
                            <p class="text-gray-800 font-semibold">{{ $application->user->semester }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="text-gray-700">
                            <span class="text-sm font-medium">Application Status:</span>
                            @if($application->status === 'pending')
                                <span class="ml-2 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                    <i class="fas fa-clock mr-1"></i>Pending Review
                                </span>
                            @elseif($application->status === 'faculty_approved')
                                <span class="ml-2 px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>Faculty Approved
                                </span>
                            @elseif($application->status === 'noc_generated')
                                <span class="ml-2 px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                    <i class="fas fa-certificate mr-1"></i>NOC Generated
                                </span>
                            @elseif($application->status === 'rejected')
                                <span class="ml-2 px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
                                    <i class="fas fa-times-circle mr-1"></i>Rejected
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Submitted: {{ $application->submitted_at ? $application->submitted_at->format('M d, Y h:i A') : $application->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Information Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-building mr-3"></i>Company Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-briefcase text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Company Name</p>
                            <p class="text-gray-800 font-semibold">{{ $application->company_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-envelope text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Company Email</p>
                            <p class="text-gray-800 font-semibold">
                                <a href="mailto:{{ $application->company_email }}" class="text-blue-600 hover:underline">
                                    {{ $application->company_email }}
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-phone text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Company Phone</p>
                            <p class="text-gray-800 font-semibold">{{ $application->company_phone }}</p>
                        </div>
                    </div>
                    @if($application->company_website)
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-globe text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Website</p>
                            <p class="text-gray-800 font-semibold">
                                <a href="{{ $application->company_website }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ $application->company_website }}
                                </a>
                            </p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-start space-x-3 md:col-span-2">
                        <i class="fas fa-map-marker-alt text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Company Address</p>
                            <p class="text-gray-800 font-semibold">{{ $application->company_address }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Internship Details Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-laptop-code mr-3"></i>Internship Details
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-user-tie text-indigo-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Position/Role</p>
                            <p class="text-gray-800 font-semibold">{{ $application->internship_position }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-calendar-check text-indigo-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Start Date</p>
                            <p class="text-gray-800 font-semibold">{{ $application->start_date->format('F d, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-calendar-times text-indigo-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">End Date</p>
                            <p class="text-gray-800 font-semibold">{{ $application->end_date->format('F d, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-hourglass-half text-indigo-500 mt-1"></i>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Duration</p>
                            <p class="text-gray-800 font-semibold">{{ $application->start_date->diffInDays($application->end_date) }} days</p>
                        </div>
                    </div>
                    @if($application->internship_description)
                    <div class="flex items-start space-x-3 md:col-span-2">
                        <i class="fas fa-align-left text-indigo-500 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-sm text-gray-500 font-medium">Description</p>
                            <p class="text-gray-800 font-semibold">{{ $application->internship_description }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-file-alt mr-3"></i>Uploaded Documents
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Company Letter -->
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center space-x-4">
                            <div class="bg-green-500 p-3 rounded-lg">
                                <i class="fas fa-file-pdf text-white text-2xl"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Company Offer Letter</p>
                                <p class="text-sm text-gray-600">Official internship offer document</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($application->company_letter_path) }}" 
                           target="_blank" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm">
                            <i class="fas fa-eye mr-2"></i>View
                        </a>
                    </div>

                    <!-- Additional Documents -->
                    @if($application->additional_documents && is_array($application->additional_documents) && count($application->additional_documents) > 0)
                        @foreach($application->additional_documents as $index => $document)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200 hover:shadow-md transition-shadow">
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-500 p-3 rounded-lg">
                                    <i class="fas fa-file text-white text-2xl"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Additional Document {{ $index + 1 }}</p>
                                    <p class="text-sm text-gray-600">Supporting document</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($document) }}" 
                               target="_blank" 
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-sm">
                                <i class="fas fa-eye mr-2"></i>View
                            </a>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>No additional documents uploaded
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approval History (if exists) -->
        @if($application->faculty_reviewed_at || $application->higher_faculty_reviewed_at)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-history mr-3"></i>Approval History
                </h3>
            </div>
            <div class="p-6 space-y-4">
                @if($application->faculty_reviewed_at)
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-semibold text-gray-800">
                            <i class="fas fa-user-check text-blue-500 mr-2"></i>Faculty Review
                        </p>
                        <span class="text-sm text-gray-600">
                            {{ $application->faculty_reviewed_at->format('M d, Y h:i A') }}
                        </span>
                    </div>
                    @if($application->faculty_remarks)
                    <p class="text-gray-700 text-sm mt-2 bg-blue-50 p-3 rounded">
                        <strong>Remarks:</strong> {{ $application->faculty_remarks }}
                    </p>
                    @endif
                </div>
                @endif

                @if($application->higher_faculty_reviewed_at)
                <div class="border-l-4 border-green-500 pl-4 py-2">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-semibold text-gray-800">
                            <i class="fas fa-user-shield text-green-500 mr-2"></i>Higher Faculty Review
                        </p>
                        <span class="text-sm text-gray-600">
                            {{ $application->higher_faculty_reviewed_at->format('M d, Y h:i A') }}
                        </span>
                    </div>
                    @if($application->higher_faculty_remarks)
                    <p class="text-gray-700 text-sm mt-2 bg-green-50 p-3 rounded">
                        <strong>Remarks:</strong> {{ $application->higher_faculty_remarks }}
                    </p>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Approval/Rejection Form (Only for Pending Applications) -->
        @if($application->status === 'pending')
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-gavel mr-3"></i>Your Decision
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Approve Form -->
                    <div class="bg-green-50 p-6 rounded-lg border-2 border-green-200">
                        <h4 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>Approve Application
                        </h4>
                        <form method="POST" action="{{ route('faculty.applications.approve', $application) }}" id="approveForm">
                            @csrf
                            <div class="mb-4">
                                <label for="approve_remarks" class="block text-sm font-medium text-gray-700 mb-2">
                                    Remarks (Optional)
                                </label>
                                <textarea 
                                    id="approve_remarks" 
                                    name="remarks" 
                                    rows="3" 
                                    class="w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm" 
                                    placeholder="Add any comments or conditions for approval..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold flex items-center justify-center">
                                <i class="fas fa-thumbs-up mr-2"></i>Approve Application
                            </button>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div class="bg-red-50 p-6 rounded-lg border-2 border-red-200">
                        <h4 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                            <i class="fas fa-times-circle mr-2"></i>Reject Application
                        </h4>
                        <form method="POST" action="{{ route('faculty.applications.reject', $application) }}" id="rejectForm">
                            @csrf
                            <div class="mb-4">
                                <label for="reject_remarks" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reason for Rejection <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    id="reject_remarks" 
                                    name="remarks" 
                                    rows="3" 
                                    class="w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm" 
                                    placeholder="Please provide a reason for rejection..." 
                                    required></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold flex items-center justify-center">
                                <i class="fas fa-thumbs-down mr-2"></i>Reject Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Back Button -->
        <div class="flex justify-end">
            <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-semibold flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    // Confirmation for approve
    document.getElementById('approveForm').addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to approve this application? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Confirmation for reject
    document.getElementById('rejectForm').addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to reject this application? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
</script>
@endsection