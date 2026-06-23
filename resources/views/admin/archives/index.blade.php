@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Breadcrumbs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <nav class="flex text-xs font-bold text-gray-500 uppercase tracking-wider mb-6">
            <a href="{{ route('admin.dashboard', ['tab' => 'mentor_mapping']) }}" class="hover:text-indigo-600 transition">
                <i class="fas fa-file-excel mr-1.5"></i>Mentor Mappings
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900">Archives Log</span>
        </nav>
    </div>

    <!-- Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header Page -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black text-gray-950 flex items-center">
                    <i class="fas fa-archive text-indigo-600 mr-3"></i>
                    Mentor Mapping Archive
                </h2>
                <p class="text-xs text-gray-500 mt-1">Review historical snapshots and restore previous mentor-student-batch configurations</p>
            </div>
            <a href="{{ route('admin.dashboard', ['tab' => 'mentor_mapping']) }}" class="px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-bold transition shadow-xs">
                Back to Upload
            </a>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 flex items-center shadow-xs">
                <i class="fas fa-check-circle text-emerald-500 text-lg mr-3"></i>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Archive Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            @if($archives->isEmpty())
                <div class="text-center py-16 bg-white">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i class="fas fa-history text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">No archived mappings found.</p>
                    <p class="text-xs text-gray-400 max-w-xs mx-auto mt-1">Archive logs are generated automatically when confirming new mapping sheets.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Import Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Imported By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">File Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Guides</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Batches</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($archives as $archive)
                                <tr class="hover:bg-gray-50/50 transition text-sm">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">
                                        {{ $archive->import_date->format('M d, Y') }}
                                        <div class="text-[10px] text-gray-400 font-medium mt-0.5">{{ $archive->import_date->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-700">
                                        {{ $archive->importedBy?->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-indigo-750">
                                        <span class="flex items-center">
                                            <i class="fas fa-file-excel text-emerald-500 mr-2 text-xs"></i>
                                            {{ $archive->file_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-800">
                                        {{ $archive->total_students }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-800">
                                        {{ $archive->total_guides }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-800">
                                        {{ $archive->total_batches }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold space-x-1.5">
                                        <a href="{{ route('admin.mentor-mapping.archives.show', $archive->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 hover:bg-gray-255 text-gray-800 rounded-lg transition">
                                            <i class="fas fa-eye mr-1"></i> View Snapshot
                                        </a>
                                        <a href="{{ route('admin.mentor-mapping.archives.download', $archive->id) }}" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">
                                            <i class="fas fa-file-download mr-1"></i> Download CSV
                                        </a>
                                        <a href="{{ route('admin.mentor-mapping.archives.show', $archive->id) }}?restore=1" class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg transition">
                                            <i class="fas fa-history mr-1"></i> Restore
                                        </a>
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
@endsection
