<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->isStudent())
                @include('dashboards.student')
            @elseif(auth()->user()->isFaculty())
                @include('dashboards.faculty')
            @elseif(auth()->user()->isHigherFaculty())
                @include('dashboards.higher-faculty')
            @endif
        </div>
    </div>
</x-app-layout>
