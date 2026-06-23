@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <!-- Top Navigation Bar -->
    @include('layouts.navigation')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('faculty.guide-dashboard') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Directory
            </a>
            <span class="text-xs text-gray-500 font-medium bg-gray-200 px-3 py-1 rounded-full">
                Monitoring Mode (Read-Only)
            </span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white">
                <div>
                    <h2 class="text-xl font-bold">Application Details</h2>
                    <p class="text-xs text-indigo-100 mt-1">Submitted on {{ $application->submitted_at ? $application->submitted_at->format('M d, Y h:i A') : $application->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div class="text-right">
                    @if($application->status === 'draft')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30">
                            <i class="fas fa-pencil-alt mr-1 text-[10px]"></i>Draft
                        </span>
                    @elseif($application->status === 'pending')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-400 text-yellow-950">
                            <i class="fas fa-clock mr-1 text-[10px]"></i>Submitted / Pending Review
                        </span>
                    @elseif($application->status === 'faculty_approved')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-blue-500 text-white">
                            <i class="fas fa-check-circle mr-1 text-[10px]"></i>Faculty Approved
                        </span>
                    @elseif($application->status === 'pending_higher')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-orange-500 text-white">
                            <i class="fas fa-hourglass-half mr-1 text-[10px]"></i>Pending Higher Review
                        </span>
                    @elseif($application->status === 'higher_faculty_approved')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-500 text-white">
                            <i class="fas fa-check-double mr-1 text-[10px]"></i>Approved
                        </span>
                    @elseif($application->status === 'noc_generated')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-teal-500 text-white">
                            <i class="fas fa-certificate mr-1 text-[10px]"></i>NOC Generated
                        </span>
                    @elseif($application->status === 'faculty_rejected')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-600 text-white">
                            <i class="fas fa-times-circle mr-1 text-[10px]"></i>Rejected by Faculty
                        </span>
                    @elseif($application->status === 'higher_faculty_rejected')
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-600 text-white">
                            <i class="fas fa-times-circle mr-1 text-[10px]"></i>Rejected by Higher Faculty
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Side - Information Columns -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Student Information Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center">
                        <i class="fas fa-user-graduate text-indigo-500 mr-2 text-lg"></i>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Student Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Full Name</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Enrollment Number</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->user->enrollment_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Email Address</p>
                                <a href="mailto:{{ $application->user->email }}" class="text-sm font-semibold text-indigo-600 hover:underline">{{ $application->user->email }}</a>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Phone Number</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->user->phone ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Semester</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->user->semester ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Batch / Program</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->user->batch ? $application->user->batch->name : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Internship Information Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center">
                        <i class="fas fa-building text-indigo-500 mr-2 text-lg"></i>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Internship Details</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Company Name</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->company_name }}</p>
                            </div>
                            @if($application->company_website)
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Company Website</p>
                                <a href="{{ $application->company_website }}" target="_blank" class="text-sm font-semibold text-indigo-600 hover:underline flex items-center">
                                    {{ $application->company_website }} <i class="fas fa-external-link-alt text-[10px] ml-1"></i>
                                </a>
                            </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-400 font-medium">HR / Contact Person Name</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->hr_name ?? $application->contact_person_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">HR Email</p>
                                @if($application->hr_email || $application->company_email)
                                <a href="mailto:{{ $application->hr_email ?? $application->company_email }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                                    {{ $application->hr_email ?? $application->company_email }}
                                </a>
                                @else
                                <p class="text-sm font-semibold text-gray-800">N/A</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">HR Contact Phone</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->hr_phone ?? $application->company_phone ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Position Offered</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->internship_position ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs text-gray-400 font-medium mb-1">Company Address</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $application->company_address }}</p>
                        </div>

                        <div class="border-t border-gray-100 pt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Start Date</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->start_date ? $application->start_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">End Date</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $application->end_date ? $application->end_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Duration</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    @if($application->start_date && $application->end_date)
                                        {{ $application->start_date->diffInDays($application->end_date) }} Days ({{ round($application->start_date->diffInWeeks($application->end_date)) }} Weeks)
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($application->internship_description)
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs text-gray-400 font-medium mb-1">Internship Description / Technology Domain</p>
                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100 font-medium">{{ $application->internship_description }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Uploaded Documents Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center">
                        <i class="fas fa-file-pdf text-indigo-500 mr-2 text-lg"></i>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Uploaded Documents</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Offer Letter -->
                        @if($application->company_letter_path)
                        <div class="flex items-center justify-between p-4 bg-indigo-50/50 rounded-xl border border-indigo-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                                    <i class="fas fa-file-pdf text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Company Offer Letter</h4>
                                    <p class="text-xs text-gray-500">Official proof of selection/internship offer</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($application->company_letter_path) }}" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition shadow-sm">
                                <i class="fas fa-external-link-alt mr-1 text-[10px]"></i>View
                            </a>
                        </div>
                        @else
                        <div class="text-center py-4 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-500 text-sm">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i> No Offer Letter uploaded.
                        </div>
                        @endif

                        <!-- Additional Documents -->
                        @if($application->additional_documents && is_array($application->additional_documents) && count($application->additional_documents) > 0)
                            @foreach($application->additional_documents as $index => $document)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-600 rounded-lg flex items-center justify-center text-white">
                                        <i class="fas fa-paperclip text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-800">Supporting Document {{ $index + 1 }}</h4>
                                        <p class="text-xs text-gray-500">Additional academic or professional reference</p>
                                    </div>
                                </div>
                                <a href="{{ Storage::url($document) }}" target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg text-xs font-semibold hover:bg-gray-700 transition shadow-sm">
                                    <i class="fas fa-external-link-alt mr-1 text-[10px]"></i>View
                                </a>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right Side - Approval Workflow Tracker & NOC Details -->
            <div class="space-y-6">
                
                <!-- Approval Tracking Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center">
                        <i class="fas fa-route text-indigo-500 mr-2 text-lg"></i>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Approval Progress</h3>
                    </div>
                    <div class="p-6">
                        <div class="relative pl-6 border-l-2 border-indigo-100 space-y-8">
                            
                            <!-- Step 1: Initial Faculty Approval -->
                            <div class="relative">
                                @php
                                    $facultyApproval = $application->approvals->where('approver_role', 'faculty')->first();
                                    $isFacultyApproved = $application->status !== 'pending' && $application->status !== 'draft' && $application->status !== 'faculty_rejected';
                                    $isFacultyRejected = $application->status === 'faculty_rejected';
                                @endphp

                                <!-- Dot indicator -->
                                <div class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full border-4 border-white flex items-center justify-center
                                    @if($isFacultyApproved) bg-green-500 ring-4 ring-green-100 @elseif($isFacultyRejected) bg-red-500 ring-4 ring-red-100 @elseif($application->status === 'pending') bg-yellow-400 ring-4 ring-yellow-100 @else bg-gray-300 @endif">
                                </div>

                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Faculty Review</h4>
                                    @if($isFacultyApproved)
                                        <p class="text-xs font-bold text-green-600 mt-0.5">Faculty Approved</p>
                                        <div class="mt-2 text-xs text-gray-500 space-y-1">
                                            <p><i class="fas fa-calendar-alt mr-1"></i> {{ $application->faculty_reviewed_at ? $application->faculty_reviewed_at->format('M d, Y') : ($facultyApproval->approved_at ? \Carbon\Carbon::parse($facultyApproval->approved_at)->format('M d, Y') : '') }}</p>
                                            <p><i class="fas fa-user-check mr-1"></i> Approved By: {{ $facultyApproval && $facultyApproval->approver ? $facultyApproval->approver->name : 'Approval Faculty' }}</p>
                                            @if($application->faculty_remarks || ($facultyApproval && $facultyApproval->remarks))
                                                <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100 mt-1 font-medium italic text-gray-600">
                                                    "{{ $application->faculty_remarks ?? $facultyApproval->remarks }}"
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($isFacultyRejected)
                                        <p class="text-xs font-bold text-red-600 mt-0.5">Rejected by Faculty</p>
                                        <div class="mt-2 text-xs text-red-700 bg-red-50 border border-red-200 p-3 rounded-lg space-y-1">
                                            <p class="font-bold"><i class="fas fa-exclamation-circle mr-1"></i> Reason for Rejection:</p>
                                            <p class="italic">"{{ $application->faculty_remarks ?? ($facultyApproval ? $facultyApproval->remarks : 'No remarks provided.') }}"</p>
                                            <div class="mt-2 pt-2 border-t border-red-200/50 text-[10px] text-red-500 font-medium">
                                                <i class="fas fa-calendar-alt mr-1"></i> Date: {{ $application->faculty_reviewed_at ? $application->faculty_reviewed_at->format('M d, Y h:i A') : ($facultyApproval->approved_at ? \Carbon\Carbon::parse($facultyApproval->approved_at)->format('M d, Y') : '') }}
                                                @if($facultyApproval && $facultyApproval->approver)
                                                    <br><i class="fas fa-user mr-1"></i> Rejected By: {{ $facultyApproval->approver->name }}
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($application->status === 'pending')
                                        <p class="text-xs text-yellow-600 font-bold mt-0.5 flex items-center">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Pending Faculty Approval
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400 font-semibold mt-0.5">Awaiting Submission</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 2: Higher Faculty Approval -->
                            <div class="relative">
                                @php
                                    $higherApproval = $application->approvals->where('approver_role', 'higher_faculty')->first();
                                    $isHigherApproved = in_array($application->status, ['higher_faculty_approved', 'noc_generated']);
                                    $isHigherRejected = $application->status === 'higher_faculty_rejected';
                                    $isHigherPending = $application->status === 'pending_higher' || ($application->status === 'faculty_approved' && $application->noc_requested);
                                @endphp

                                <!-- Dot indicator -->
                                <div class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full border-4 border-white flex items-center justify-center
                                    @if($isHigherApproved) bg-green-500 ring-4 ring-green-100 @elseif($isHigherRejected) bg-red-500 ring-4 ring-red-100 @elseif($isHigherPending) bg-yellow-400 ring-4 ring-yellow-100 @else bg-gray-300 @endif">
                                </div>

                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Higher Faculty Review</h4>
                                    @if($isHigherApproved)
                                        <p class="text-xs font-bold text-green-600 mt-0.5">Approved by Higher Faculty</p>
                                        <div class="mt-2 text-xs text-gray-500 space-y-1">
                                            <p><i class="fas fa-calendar-alt mr-1"></i> {{ $application->higher_faculty_reviewed_at ? $application->higher_faculty_reviewed_at->format('M d, Y') : ($higherApproval->approved_at ? \Carbon\Carbon::parse($higherApproval->approved_at)->format('M d, Y') : '') }}</p>
                                            <p><i class="fas fa-user-shield mr-1"></i> Approved By: {{ $higherApproval && $higherApproval->approver ? $higherApproval->approver->name : 'NOC Authority' }}</p>
                                            @if($application->higher_faculty_remarks || ($higherApproval && $higherApproval->remarks))
                                                <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100 mt-1 font-medium italic text-gray-600">
                                                    "{{ $application->higher_faculty_remarks ?? $higherApproval->remarks }}"
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($isHigherRejected)
                                        <p class="text-xs font-bold text-red-600 mt-0.5">Rejected by Higher Faculty</p>
                                        <div class="mt-2 text-xs text-red-700 bg-red-50 border border-red-200 p-3 rounded-lg space-y-1">
                                            <p class="font-bold"><i class="fas fa-exclamation-circle mr-1"></i> Reason for Rejection:</p>
                                            <p class="italic">"{{ $application->higher_faculty_remarks ?? ($higherApproval ? $higherApproval->remarks : 'No remarks provided.') }}"</p>
                                            <div class="mt-2 pt-2 border-t border-red-200/50 text-[10px] text-red-500 font-medium">
                                                <i class="fas fa-calendar-alt mr-1"></i> Date: {{ $application->higher_faculty_reviewed_at ? $application->higher_faculty_reviewed_at->format('M d, Y h:i A') : ($higherApproval->approved_at ? \Carbon\Carbon::parse($higherApproval->approved_at)->format('M d, Y') : '') }}
                                                @if($higherApproval && $higherApproval->approver)
                                                    <br><i class="fas fa-user mr-1"></i> Rejected By: {{ $higherApproval->approver->name }}
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($isHigherPending)
                                        <p class="text-xs text-yellow-600 font-bold mt-0.5 flex items-center">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Pending Higher Review
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400 font-semibold mt-0.5">Pending Faculty Approval First</p>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- NOC Generation Information Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center">
                        <i class="fas fa-certificate text-indigo-500 mr-2 text-lg"></i>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">NOC Information</h3>
                    </div>
                    <div class="p-6">
                        @if($application->noc)
                            <div class="space-y-4">
                                <div class="bg-teal-50 border border-teal-200 rounded-xl p-4">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <div class="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center text-white">
                                            <i class="fas fa-file-contract"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-teal-600 uppercase font-bold tracking-wider">No Objection Certificate</p>
                                            <p class="text-sm font-bold text-teal-900">Generated & Ready</p>
                                        </div>
                                    </div>
                                    <div class="text-xs text-teal-800 space-y-1">
                                        <p><span class="font-bold">NOC Number:</span> {{ $application->noc->noc_number }}</p>
                                        <p><span class="font-bold">Generation Date:</span> {{ $application->noc->generated_at ? $application->noc->generated_at->format('M d, Y') : $application->noc->created_at->format('M d, Y') }}</p>
                                        @if($application->noc->generator)
                                            <p><span class="font-bold">Generated By:</span> {{ $application->noc->generator->name }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if($application->noc->pdf_path)
                                <a href="{{ url('storage/' . $application->noc->pdf_path) }}" target="_blank"
                                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-bold transition shadow-sm">
                                    <i class="fas fa-download mr-2"></i>Download Generated NOC
                                </a>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                <i class="fas fa-file-invoice text-3xl text-gray-300 mb-2 block"></i>
                                <span class="text-xs text-gray-500 font-semibold">
                                    @if($application->noc_requested)
                                        NOC has been requested by student. Pending generation by Higher Faculty.
                                    @else
                                        NOC has not been requested or generated yet.
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
