@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Breadcrumbs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <nav class="flex text-xs font-bold text-gray-500 uppercase tracking-wider mb-6">
            <a href="{{ route('admin.student-directory.index', ['tab' => 'mentor_mapping']) }}" class="hover:text-indigo-600 transition">
                <i class="fas fa-file-excel mr-1.5"></i>Mentor Mappings
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="{{ route('admin.mentor-mapping.archives') }}" class="hover:text-indigo-600 transition">Archives Log</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900">Compare Snapshots</span>
        </nav>
    </div>

    <!-- Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Header Page -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black text-gray-950 flex items-center">
                    <i class="fas fa-exchange-alt text-indigo-600 mr-3"></i>
                    Compare Archive Snapshots
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Comparing Base: <span class="font-bold text-gray-800">{{ $archive->file_name }}</span> ({{ $archive->import_date->format('M d, Y h:i A') }})
                    with Target: <span class="font-bold text-gray-800">{{ $other->file_name }}</span> ({{ $other->import_date->format('M d, Y h:i A') }})
                </p>
            </div>
            <a href="{{ route('admin.mentor-mapping.archives') }}" class="px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-bold transition shadow-sm">
                Back to Archives
            </a>
        </div>

        <!-- Summary Diff Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-emerald-800">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider">Added Students</span>
                    <i class="fas fa-user-plus text-emerald-500 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black mt-2">{{ count($added) }}</h3>
            </div>
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-rose-800">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider">Removed Students</span>
                    <i class="fas fa-user-minus text-rose-500 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black mt-2">{{ count($removed) }}</h3>
            </div>
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 text-indigo-800">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider">Guide Changes</span>
                    <i class="fas fa-user-tie text-indigo-500 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black mt-2">{{ count($guideChanges) }}</h3>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-amber-850 font-semibold">
                <div class="flex items-center justify-between text-amber-800">
                    <span class="text-xs font-bold uppercase tracking-wider">Batch Changes</span>
                    <i class="fas fa-layer-group text-amber-550 text-lg"></i>
                </div>
                <h3 class="text-3xl font-black mt-2 text-amber-800">{{ count($batchChanges) }}</h3>
            </div>
        </div>

        <!-- Details tabs using Alpine -->
        <div x-data="{ subTab: 'added' }" class="space-y-4">
            <!-- Tabs Menu -->
            <div class="flex border-b border-gray-200 gap-2 bg-white p-1.5 rounded-xl shadow-xs">
                <button @click="subTab = 'added'" :class="subTab === 'added' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-user-plus"></i> Added ({{ count($added) }})
                </button>
                <button @click="subTab = 'removed'" :class="subTab === 'removed' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-user-minus"></i> Removed ({{ count($removed) }})
                </button>
                <button @click="subTab = 'guides'" :class="subTab === 'guides' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-user-tie"></i> Guide Changes ({{ count($guideChanges) }})
                </button>
                <button @click="subTab = 'batches'" :class="subTab === 'batches' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-layer-group"></i> Batch Changes ({{ count($batchChanges) }})
                </button>
            </div>

            <!-- Tab Added -->
            <div x-show="subTab === 'added'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                @if(empty($added))
                    <div class="text-center py-12 text-gray-500 font-medium text-sm">No students added.</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment Number</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Guide</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($added as $row)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $row['enrollment'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">{{ $row['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $row['batch'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">{{ $row['guide'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Tab Removed -->
            <div x-show="subTab === 'removed'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                @if(empty($removed))
                    <div class="text-center py-12 text-gray-500 font-medium text-sm">No students removed.</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment Number</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned Guide</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($removed as $row)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">{{ $row['enrollment'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">{{ $row['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $row['batch'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">{{ $row['guide'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Tab Guide Changes -->
            <div x-show="subTab === 'guides'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                @if(empty($guideChanges))
                    <div class="text-center py-12 text-gray-500 font-medium text-sm">No guide assignment changes.</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Base Guide</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Target Guide</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($guideChanges as $row)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $row['enrollment'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">{{ $row['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-rose-600 font-medium">{{ $row['old_guide'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-emerald-600 font-bold">
                                        <i class="fas fa-long-arrow-alt-right mr-1.5"></i>{{ $row['new_guide'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Tab Batch Changes -->
            <div x-show="subTab === 'batches'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                @if(empty($batchChanges))
                    <div class="text-center py-12 text-gray-500 font-medium text-sm">No batch assignment changes.</div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Enrollment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Base Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Target Batch</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm">
                            @foreach($batchChanges as $row)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-900">{{ $row['enrollment'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">{{ $row['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-rose-600 font-medium">{{ $row['old_batch'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-emerald-600 font-bold">
                                        <i class="fas fa-long-arrow-alt-right mr-1.5"></i>{{ $row['new_batch'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
