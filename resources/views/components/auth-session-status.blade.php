@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-4 bg-green-50 border border-green-200 rounded-xl']) }}>
        <p class="font-medium text-sm text-green-700 flex items-center space-x-2">
            <i class="fas fa-check-circle"></i>
            <span>{{ $status }}</span>
        </p>
    </div>
@endif
