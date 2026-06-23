@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12" x-data="{
    showRestoreModal: {{ request('restore') ? 'true' : 'false' }},
    confirmationText: ''
}">
    <!-- Breadcrumbs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <nav class="flex text-xs font-bold text-gray-500 uppercase tracking-wider mb-6">
            <a href="{{ route('admin.dashboard', ['tab' => 'mentor_mapping']) }}" class="hover:text-indigo-600 transition">
                <i class="fas fa-file-excel mr-1.5"></i>Mentor Mappings
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="{{ route('admin.mentor-mapping.archives') }}" class="hover:text-indigo-600 transition">
                Archives Log
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900">Snapshot #{{ $archive->id }}</span>
        </nav>
    </div>

    <!-- Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-955 flex items-center">
                    <i class="fas fa-history text-indigo-650 mr-3"></i>
                    Archive Snapshot Details
                </h2>
                <p class="text-xs text-gray-500 mt-1">Snapshot of student, guide and batch mapping configurations from import: {{ $archive->file_name }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.mentor-mapping.archives.download', $archive->id) }}" class="px-4 py-2 border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                    <i class="fas fa-file-download text-xs"></i> Download CSV Report
                </a>
                <button @click="showRestoreModal = true" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow flex items-center gap-1.5">
                    <i class="fas fa-history text-xs"></i> Restore Mappings
                </button>
            </div>
        </div>

        <!-- Flash messages -->
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 flex items-center shadow-xs">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg mr-3"></i>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Snapshot Info Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Details Metadata -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h4 class="text-indigo-950 font-black text-xs uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-2 text-md"></i>
                    Snapshot Metadata
                </h4>
                
                <div class="space-y-3.5 text-xs">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Import Date</p>
                        <p class="font-bold text-gray-800 mt-0.5">{{ $archive->import_date->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Imported By</p>
                        <p class="font-bold text-gray-800 mt-0.5">{{ $archive->importedBy?->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Excel File Name</p>
                        <p class="font-bold text-indigo-900 mt-0.5 flex items-center">
                            <i class="fas fa-file-excel text-emerald-500 mr-1.5"></i>
                            {{ $archive->file_name }}
                        </p>
                    </div>
                    @if($archive->import_notes)
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Import Notes</p>
                            <p class="font-medium text-gray-600 mt-0.5 bg-gray-50 border border-gray-150 p-2.5 rounded-lg italic">
                                {{ $archive->import_notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stats Aggregate Card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm lg:col-span-2 space-y-4 flex flex-col justify-between">
                <div>
                    <h4 class="text-indigo-950 font-black text-xs uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center">
                        <i class="fas fa-database text-indigo-600 mr-2 text-md"></i>
                        Statistical Aggregates
                    </h4>
                    
                    <div class="grid grid-cols-3 gap-4 pt-4 text-center">
                        <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Total Students</p>
                            <p class="text-2xl font-black text-gray-800 mt-1">{{ $archive->total_students }}</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Total Guides</p>
                            <p class="text-2xl font-black text-gray-800 mt-1">{{ $archive->total_guides }}</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-150 p-4 rounded-xl">
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Total Batches</p>
                            <p class="text-2xl font-black text-gray-800 mt-1">{{ $archive->total_batches }}</p>
                        </div>
                    </div>
                </div>

                <div class="text-[11px] font-semibold text-rose-650 bg-rose-50 border border-rose-100 p-3 rounded-lg flex items-center gap-1.5">
                    <i class="fas fa-exclamation-triangle text-xs text-rose-500"></i>
                    Restoring this snapshot will overwrite current guide and batch assignments for all student records below.
                </div>
            </div>

        </div>

        <!-- Student Assignment Snapshot Table Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-md font-bold text-gray-900">Archived Student Assignments</h3>
                <p class="text-xs text-gray-500">State of Student, batch cohort and guide assignments at time of import</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment Number</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived Guide</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($items as $item)
                            <tr class="hover:bg-gray-50/50 transition text-sm">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">
                                    {{ $item->enrollment_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    {{ $item->student_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600">
                                        {{ $item->batch_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-700 flex items-center">
                                    @if($item->guide_name && $item->guide_name !== 'N/A')
                                        <i class="fas fa-chalkboard-teacher text-indigo-500 mr-1.5 text-xs"></i>
                                        {{ $item->guide_name }}
                                    @else
                                        <span class="text-gray-400 italic font-normal">Unassigned</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL: RESTORE CONFIRMATION -->
    <div x-show="showRestoreModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-xs px-4" x-transition>
        <div class="bg-white rounded-2xl shadow-xl border border-gray-150 max-w-md w-full overflow-hidden" @click.away="showRestoreModal = false">
            <div class="bg-rose-600 text-white px-6 py-4 flex justify-between items-center">
                <h4 class="font-bold flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-rose-200"></i>Restore Archived Mappings
                </h4>
                <button @click="showRestoreModal = false" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.mentor-mapping.archives.restore', $archive->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div class="bg-rose-50 border border-rose-150 p-4 rounded-xl text-xs text-rose-850 space-y-1.5">
                    <p class="font-bold uppercase tracking-wider text-[10px]">Warning</p>
                    <p>You are about to permanently overwrite the current student batch and guide mappings in the database with the configurations from this archive snapshot.</p>
                    <p class="font-semibold pt-1">Target Archive Name: <strong class="text-rose-900">{{ $archive->file_name }}</strong></p>
                    <p class="font-semibold">Target Archive Date: <strong class="text-rose-900">{{ $archive->import_date->format('M d, Y') }}</strong></p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        To confirm, type <strong class="text-rose-600">RESTORE</strong> exactly:
                    </label>
                    <input 
                        type="text" 
                        name="confirmation_text" 
                        x-model="confirmationText"
                        placeholder="Type RESTORE to continue"
                        required
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-rose-500 outline-none text-gray-800 font-bold shadow-xs placeholder-gray-400"
                    >
                </div>

                <div class="pt-4 flex space-x-2 border-t border-gray-100">
                    <button type="button" @click="showRestoreModal = false" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg font-bold text-sm transition">
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="confirmationText !== 'RESTORE'"
                        class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:bg-rose-300 text-white rounded-lg font-bold text-sm transition shadow-sm"
                    >
                        Restore Snapshot
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
