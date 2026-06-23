@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-4xl w-full space-y-8 text-center">
        <!-- Header -->
        <div class="space-y-3">
            <div class="inline-flex p-3 bg-indigo-100 text-indigo-600 rounded-full shadow-sm">
                <i class="fas fa-user-shield text-3xl animate-pulse"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                Welcome, {{ $user->name }}
            </h2>
            <p class="text-sm text-gray-500 max-w-md mx-auto">
                You hold multiple active responsibilities. Please select which dashboard workspace you want to enter:
            </p>
        </div>

        <!-- Cards Container -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 max-w-3xl mx-auto">
            <!-- Guide Dashboard Card -->
            @if(in_array('guide', $permissions))
                <a href="{{ route('switch-dashboard', 'guide') }}" 
                   class="group relative bg-white border border-gray-200 rounded-2xl p-6 shadow-xs hover:shadow-xl hover:border-indigo-500 hover:-translate-y-1 transition duration-300 flex flex-col text-left">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition duration-300">
                        <i class="fas fa-user-tie text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-indigo-600 transition">
                        Guide Dashboard
                    </h3>
                    <p class="text-xs text-gray-500 flex-grow leading-relaxed">
                        Manage your assigned students, batches, and review their progress reports.
                    </p>
                    <div class="mt-4 flex items-center text-xs font-bold text-indigo-600 group-hover:translate-x-1 transition duration-300">
                        <span>Go to Workspace</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </div>
                </a>
            @endif

            <!-- Approval Dashboard Card -->
            @if(in_array('approval_faculty', $permissions))
                <a href="{{ route('switch-dashboard', 'approval_faculty') }}" 
                   class="group relative bg-white border border-gray-200 rounded-2xl p-6 shadow-xs hover:shadow-xl hover:border-emerald-500 hover:-translate-y-1 transition duration-300 flex flex-col text-left">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition duration-300">
                        <i class="fas fa-check-circle text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-emerald-600 transition">
                        Approval Dashboard
                    </h3>
                    <p class="text-xs text-gray-500 flex-grow leading-relaxed">
                        Review, approve, or reject student internship applications at the department level.
                    </p>
                    <div class="mt-4 flex items-center text-xs font-bold text-emerald-600 group-hover:translate-x-1 transition duration-300">
                        <span>Go to Workspace</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </div>
                </a>
            @endif

            <!-- NOC Dashboard Card -->
            @if(in_array('noc_authority', $permissions))
                <a href="{{ route('switch-dashboard', 'noc_authority') }}" 
                   class="group relative bg-white border border-gray-200 rounded-2xl p-6 shadow-xs hover:shadow-xl hover:border-rose-500 hover:-translate-y-1 transition duration-300 flex flex-col text-left">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 group-hover:bg-rose-600 group-hover:text-white transition duration-300">
                        <i class="fas fa-stamp text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-rose-600 transition">
                        NOC Dashboard
                    </h3>
                    <p class="text-xs text-gray-500 flex-grow leading-relaxed">
                        Review faculty-approved internship submissions and generate official NOC certificates.
                    </p>
                    <div class="mt-4 flex items-center text-xs font-bold text-rose-600 group-hover:translate-x-1 transition duration-300">
                        <span>Go to Workspace</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </div>
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
