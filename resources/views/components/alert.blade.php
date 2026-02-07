@props(['type' => 'info', 'message'])

@php
    $classes = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    ];

    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle',
    ];
@endphp

<div class="border px-4 py-3 rounded relative {{ $classes[$type] }}" role="alert" x-data="{ show: true }" x-show="show" x-transition>
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas {{ $icons[$type] }} mr-2"></i>
            <span class="block sm:inline">{{ $message ?? $slot }}</span>
        </div>
        <button @click="show = false" class="ml-4">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>