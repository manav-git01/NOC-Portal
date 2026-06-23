@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12" x-data="{
    showChangeBatchModal: false,
    showChangeGuideModal: false,
    showChangeBatchGuideModal: false,
    targetStudent: { id: null, name: '', enrollment: '', batch_id: null, guide_id: null }
}">
    <!-- Navigation Breadcrumbs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <nav class="flex text-xs font-bold text-gray-500 uppercase tracking-wider mb-6">
            <a href="{{ route('admin.dashboard', ['tab' => 'batches']) }}" class="hover:text-indigo-600 transition">
                <i class="fas fa-layer-group mr-1.5"></i>Batch Directory
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900">Manage Batch: {{ $batch->name }}</span>
        </nav>
    </div>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 flex items-center shadow-xs">
                <i class="fas fa-check-circle text-emerald-500 text-lg mr-3"></i>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 flex items-center shadow-xs">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg mr-3"></i>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Batch Information & Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Cohort Details Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between lg:col-span-1">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-bold text-xl">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-gray-955">{{ $batch->name }}</h2>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Academic Cohort</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Current Designated Guide</p>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-800 text-sm">
                                    {{ $batch->guide?->name ?? 'None Assigned' }}
                                </span>
                                <button @click="showChangeBatchGuideModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    Change
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Total Students</p>
                                <p class="text-2xl font-black text-gray-800 mt-1">{{ $totalStudents }}</p>
                            </div>
                            <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Active Mappings</p>
                                <p class="text-2xl font-black text-emerald-600 mt-1">
                                    {{ $students->whereNotNull('guide_id')->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Internship Stats Cards -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm lg:col-span-2 space-y-4 flex flex-col justify-between">
                <div>
                    <h3 class="text-indigo-950 font-bold flex items-center border-b border-gray-100 pb-3 text-sm">
                        <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                        Internship & NOC Aggregates
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4">
                        <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 text-center">
                            <p class="text-[10px] text-indigo-600 font-bold uppercase">Internship Applications</p>
                            <p class="text-2xl font-black text-indigo-950 mt-1">{{ $applicationsCount }}</p>
                            <p class="text-[9px] text-gray-400 mt-1">Submitted applications</p>
                        </div>
                        <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 text-center">
                            <p class="text-[10px] text-emerald-600 font-bold uppercase">Approved by Faculty</p>
                            <p class="text-2xl font-black text-emerald-700 mt-1">{{ $approvedCount }}</p>
                            <p class="text-[9px] text-gray-400 mt-1">Awaiting NOC generation</p>
                        </div>
                        <div class="bg-teal-50/50 border border-teal-100 rounded-xl p-4 text-center">
                            <p class="text-[10px] text-teal-650 font-bold uppercase">NOCs Generated</p>
                            <p class="text-2xl font-black text-teal-700 mt-1">{{ $nocGeneratedCount }}</p>
                            <p class="text-[9px] text-gray-400 mt-1">Finalized NOC certificates</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-[11px] font-medium text-gray-400 flex items-center">
                    <i class="fas fa-info-circle text-indigo-500 mr-1.5 text-xs"></i>
                    Changing the batch guide below will apply the change to all students currently registered in this cohort.
                </div>
            </div>

        </div>

        <!-- Student Management Table Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-md font-bold text-gray-900">Students Registered</h3>
                    <p class="text-xs text-gray-500">View and update individual batch cohorts and guide associations</p>
                </div>
            </div>

            @if($students->isEmpty())
                <div class="text-center py-16 bg-white">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">No students registered in this batch.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment Number</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Guide</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Application Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NOC Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($students as $student)
                                @php 
                                    $app = $student->internshipApplications->first(); 
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition text-sm">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">
                                        {{ $student->enrollment_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">{{ $student->name }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium mt-0.5">{{ $student->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-750 font-medium">
                                        @if($student->guide)
                                            <span class="flex items-center">
                                                <i class="fas fa-chalkboard-teacher text-indigo-500 mr-1.5 text-xs"></i>
                                                {{ $student->guide->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($app)
                                            @if($app->isPending() || $app->isPendingHigher())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-100">
                                                    {{ $app->isPending() ? 'Pending Faculty' : 'Pending Higher' }}
                                                </span>
                                            @elseif($app->isFacultyApproved() || $app->isHigherFacultyApproved())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    Approved
                                                </span>
                                            @elseif($app->hasNoc())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    NOC Generated
                                                </span>
                                            @elseif($app->isRejected())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                                    Rejected
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200">
                                                Not Submitted
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($app && $app->noc)
                                            <span class="inline-flex items-center text-xs font-semibold text-emerald-700">
                                                <i class="fas fa-file-signature mr-1.5 text-emerald-500"></i>Generated
                                            </span>
                                        @elseif($app && $app->noc_requested)
                                            <span class="inline-flex items-center text-xs font-semibold text-amber-700">
                                                <i class="fas fa-clock mr-1.5 text-amber-500"></i>Requested
                                            </span>
                                        @elseif($app && $app->isFacultyApproved())
                                            <span class="inline-flex items-center text-xs font-semibold text-indigo-700">
                                                <i class="fas fa-check mr-1.5 text-indigo-500"></i>Eligible
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs font-semibold text-gray-400">
                                                <i class="fas fa-ban mr-1.5"></i>Not Eligible
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold space-x-1.5">
                                        <button 
                                            @click="
                                                targetStudent = {
                                                    id: {{ $student->id }},
                                                    name: '{{ $student->name }}',
                                                    enrollment: '{{ $student->enrollment_number }}',
                                                    batch_id: {{ $student->batch_id ?? 'null' }},
                                                    guide_id: {{ $student->guide_id ?? 'null' }}
                                                };
                                                showChangeBatchModal = true;
                                            "
                                            class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 hover:bg-gray-250 text-gray-800 rounded-lg transition"
                                        >
                                            <i class="fas fa-exchange-alt mr-1"></i> Reassign Batch
                                        </button>
                                        <button 
                                            @click="
                                                targetStudent = {
                                                    id: {{ $student->id }},
                                                    name: '{{ $student->name }}',
                                                    enrollment: '{{ $student->enrollment_number }}',
                                                    batch_id: {{ $student->batch_id ?? 'null' }},
                                                    guide_id: {{ $student->guide_id ?? 'null' }}
                                                };
                                                showChangeGuideModal = true;
                                            "
                                            class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition"
                                        >
                                            <i class="fas fa-user-edit mr-1"></i> Reassign Guide
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL 1: CHANGE STUDENT BATCH -->
    <div x-show="showChangeBatchModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs px-4" x-transition>
        <div class="bg-white rounded-2xl shadow-xl border border-gray-150 max-w-md w-full overflow-hidden" @click.away="showChangeBatchModal = false">
            <div class="bg-indigo-900 text-white px-6 py-4 flex justify-between items-center">
                <h4 class="font-bold flex items-center">
                    <i class="fas fa-exchange-alt mr-2 text-indigo-400"></i>Reassign Student Batch
                </h4>
                <button @click="showChangeBatchModal = false" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form :action="'{{ url('admin/students') }}/' + targetStudent.id + '/change-batch'" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl space-y-1">
                    <p class="text-[10px] text-gray-400 font-bold uppercase">Target Student</p>
                    <p class="font-bold text-gray-800 text-sm" x-text="targetStudent.name + ' (' + targetStudent.enrollment + ')'"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Cohort / Batch</label>
                    <select name="batch_id" required class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none text-gray-800 font-semibold shadow-xs">
                        <option value="" disabled selected>Select Batch...</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex space-x-2 border-t border-gray-100">
                    <button type="button" @click="showChangeBatchModal = false" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg font-bold text-sm transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-650 hover:bg-indigo-700 text-white rounded-lg font-bold text-sm transition shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: CHANGE STUDENT GUIDE -->
    <div x-show="showChangeGuideModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs px-4" x-transition>
        <div class="bg-white rounded-2xl shadow-xl border border-gray-150 max-w-md w-full overflow-hidden" @click.away="showChangeGuideModal = false">
            <div class="bg-indigo-900 text-white px-6 py-4 flex justify-between items-center">
                <h4 class="font-bold flex items-center">
                    <i class="fas fa-user-edit mr-2 text-indigo-400"></i>Reassign Student Guide
                </h4>
                <button @click="showChangeGuideModal = false" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form :action="'{{ url('admin/students') }}/' + targetStudent.id + '/change-guide'" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl space-y-1">
                    <p class="text-[10px] text-gray-400 font-bold uppercase">Target Student</p>
                    <p class="font-bold text-gray-800 text-sm" x-text="targetStudent.name + ' (' + targetStudent.enrollment + ')'"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Faculty Guide</label>
                    <select name="guide_id" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none text-gray-800 font-semibold shadow-xs">
                        <option value="">Unassign Guide</option>
                        @foreach($faculty as $fac)
                            <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex space-x-2 border-t border-gray-100">
                    <button type="button" @click="showChangeGuideModal = false" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg font-bold text-sm transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-650 hover:bg-indigo-700 text-white rounded-lg font-bold text-sm transition shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: CHANGE BATCH GUIDE -->
    <div x-show="showChangeBatchGuideModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs px-4" x-transition>
        <div class="bg-white rounded-2xl shadow-xl border border-gray-150 max-w-md w-full overflow-hidden" @click.away="showChangeBatchGuideModal = false">
            <div class="bg-amber-600 text-white px-6 py-4 flex justify-between items-center">
                <h4 class="font-bold flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-amber-200"></i>Reassign Cohort Guide
                </h4>
                <button @click="showChangeBatchGuideModal = false" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.batches.change-guide', $batch->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-amber-50 border border-amber-250 p-4 rounded-xl text-xs text-amber-850 space-y-1">
                    <p class="font-bold uppercase tracking-wider text-[10px]">Warning</p>
                    <p>This action will update all students currently registered in the batch <strong>{{ $batch->name }}</strong>.</p>
                    <p class="font-bold text-sm pt-2">Students Affected: {{ $totalStudents }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Cohort Guide</label>
                    <select name="guide_id" required class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none text-gray-800 font-semibold shadow-xs">
                        <option value="" disabled selected>Select Guide...</option>
                        @foreach($faculty as $fac)
                            <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex space-x-2 border-t border-gray-100">
                    <button type="button" @click="showChangeBatchGuideModal = false" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg font-bold text-sm transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-amber-650 hover:bg-amber-700 text-white rounded-lg font-bold text-sm transition shadow-sm">
                        Confirm Reassignment
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
