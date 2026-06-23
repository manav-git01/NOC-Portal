@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Application Details</h1>
            <p class="text-gray-600 mt-1">View your internship application information</p>
        </div>

        <!-- Status Banner -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 border-l-4 {{ $application->status === 'pending' ? 'border-yellow-500' : ($application->status === 'noc_generated' ? 'border-green-500' : ($application->isRejected() ? 'border-red-500' : 'border-blue-500')) }}">
            <div class="p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                            Application Status
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <i class="far fa-calendar mr-1"></i>
                            Submitted on {{ $application->submitted_at->format('F d, Y \a\t h:i A') }}
                        </p>
                    </div>
                    <div>
                        @php
                            $statusConfig = [
                                'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock'],
                                'faculty_approved' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-check'],
                                'faculty_rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times'],
                                'higher_faculty_approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-double'],
                                'higher_faculty_rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times'],
                                'noc_generated' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-certificate'],
                            ];
                            $config = $statusConfig[$application->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question'];
                        @endphp
                        <div class="flex gap-3 flex-wrap">
                            <span class="px-4 py-2 inline-flex items-center text-sm leading-5 font-semibold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                <i class="fas {{ $config['icon'] }} mr-2"></i>
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                            @if($application->noc_requested)
                                <span class="px-4 py-2 inline-flex items-center text-sm leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    <i class="fas fa-file-invoice mr-2"></i>
                                    NOC Requested
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($application->status === 'noc_generated')
                    <div class="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <p class="text-green-800 font-semibold flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    🎉 Your NOC has been generated!
                                </p>
                                <p class="text-sm text-green-700 mt-1">You can now download your No Objection Certificate.</p>
                            </div>
                            <a href="{{ route('student.applications.download-noc', $application) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition-all duration-200 shadow-md">
                                <i class="fas fa-download mr-2"></i>
                                Download NOC
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Company Information -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-building mr-2"></i>
                    Company Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-building w-4 mr-2"></i>
                            Company Name
                        </p>
                        <p class="font-semibold text-gray-900">{{ $application->company_name }}</p>
                    </div>
                    
                    @if($application->company_email)
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-envelope w-4 mr-2"></i>
                            Email
                        </p>
                        <p class="font-semibold text-gray-900">
                            <a href="mailto:{{ $application->company_email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $application->company_email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    
                    @if($application->company_phone)
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-phone w-4 mr-2"></i>
                            Phone
                        </p>
                        <p class="font-semibold text-gray-900">{{ $application->company_phone }}</p>
                    </div>
                    @endif
                    
                    @if($application->company_website)
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-globe w-4 mr-2"></i>
                            Website
                        </p>
                        <p class="font-semibold text-gray-900">
                            <a href="{{ $application->company_website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                {{ $application->company_website }}
                            </a>
                        </p>
                    </div>
                    @endif
                    
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                            Address
                        </p>
                        <p class="font-semibold text-gray-900">{{ $application->company_address }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Internship Details -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-briefcase mr-2"></i>
                    Internship Details
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-user-tie w-4 mr-2"></i>
                            Position/Role
                        </p>
                        <p class="font-semibold text-gray-900">{{ $application->internship_position }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-clock w-4 mr-2"></i>
                            Duration
                        </p>
                        <p class="font-semibold text-gray-900">
                            {{ $application->start_date->diffInDays($application->end_date) }} days
                        </p>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-calendar-alt w-4 mr-2"></i>
                            Start Date
                        </p>
                        <p class="font-semibold text-gray-900">{{ $application->start_date->format('F d, Y') }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-calendar-check w-4 mr-2"></i>
                            End Date
                        </p>
                        <p class="font-semibold text-gray-900">{{ $application->end_date->format('F d, Y') }}</p>
                    </div>
                    
                    @if($application->internship_description)
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-align-left w-4 mr-2"></i>
                            Description
                        </p>
                        <p class="font-semibold text-gray-900">{{ $application->internship_description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center">
                    <i class="fas fa-file-alt mr-2"></i>
                    Documents
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <!-- Company Letter -->
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-file-pdf text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Company Offer Letter</p>
                                <p class="text-sm text-gray-600">Required document</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($application->company_letter_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-eye mr-2"></i>
                            View
                        </a>
                    </div>

                    <!-- Additional Documents -->
                    @if($application->additional_documents && is_array($application->additional_documents) && count($application->additional_documents) > 0)
                        @foreach($application->additional_documents as $index => $document)
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-slate-50 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gray-600 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-file text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">Additional Document {{ $index + 1 }}</p>
                                        <p class="text-sm text-gray-600">Supporting document</p>
                                    </div>
                                </div>
                                <a href="{{ Storage::url($document) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                    <i class="fas fa-eye mr-2"></i>
                                    View
                                </a>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            No additional documents uploaded
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approval History -->
        @if($application->approvals && $application->approvals->count() > 0)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-history mr-2"></i>
                        Approval History
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($application->approvals as $approval)
                            <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    @if($approval->status === 'approved')
                                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-green-600 text-xl"></i>
                                        </div>
                                    @else
                                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-times text-red-600 text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <p class="font-semibold text-gray-900">{{ $approval->approver->name }}</p>
                                        <p class="text-sm text-gray-600">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ $approval->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-user-tag mr-1"></i>
                                        {{ ucfirst($approval->approver->role->name) }}
                                    </p>
                                    <p class="mt-2 text-sm">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full font-semibold {{ $approval->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($approval->status) }}
                                        </span>
                                    </p>
                                    @if($approval->remarks)
                                        <div class="mt-3 p-3 bg-white rounded-lg border border-gray-200">
                                            <p class="text-sm text-gray-700">
                                                <i class="fas fa-comment-alt mr-2 text-gray-400"></i>
                                                {{ $approval->remarks }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Remarks Section -->
        @if($application->faculty_remarks || $application->higher_faculty_remarks)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-comments mr-2"></i>
                        Remarks
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($application->faculty_remarks)
                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <p class="font-semibold text-blue-900 mb-2">
                                <i class="fas fa-user-graduate mr-2"></i>
                                Faculty Remarks
                            </p>
                            <p class="text-gray-700">{{ $application->faculty_remarks }}</p>
                        </div>
                    @endif
                    
                    @if($application->higher_faculty_remarks)
                        <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <p class="font-semibold text-purple-900 mb-2">
                                <i class="fas fa-user-tie mr-2"></i>
                                Higher Faculty Remarks
                            </p>
                            <p class="text-gray-700">{{ $application->higher_faculty_remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="space-y-4">
            <div class="flex items-center justify-center gap-4 flex-wrap">
                @if($application->status === 'noc_generated')
                    <a href="{{ route('student.applications.download-noc', $application) }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-xl font-bold text-base text-white uppercase tracking-wider hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="fas fa-download text-xl mr-3"></i>
                        Download NOC
                    </a>
                @endif
                @if($application->status === 'faculty_approved' && !$application->noc_requested)
                    <form action="{{ route('student.applications.request-noc', $application) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 border border-transparent rounded-xl font-bold text-base text-white uppercase tracking-wider hover:from-purple-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            <i class="fas fa-file-invoice text-xl mr-3"></i>
                            Request NOC
                        </button>
                    </form>
                @endif
            </div>
            
            <div class="flex justify-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-gray-200 border border-transparent rounded-lg font-semibold text-sm text-gray-700 uppercase tracking-wider hover:bg-gray-300 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection