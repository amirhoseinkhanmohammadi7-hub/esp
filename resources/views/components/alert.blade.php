@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => true,
])

@php
$colors = [
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'error' => 'bg-red-50 text-red-800 border-red-200',
    'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
];

$icons = [
    'success' => '✓',
    'error' => '✕',
    'warning' => '⚠',
    'info' => 'ℹ',
];

$colorClass = $colors[$type] ?? $colors['info'];
$icon = $icons[$type] ?? $icons['info'];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-transition
    class="{{ $colorClass }} border rounded-lg p-4 mb-4"
    role="alert"
>
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <span class="text-lg font-bold">{{ $icon }}</span>
        </div>
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-medium mb-1">{{ $title }}</h3>
            @endif
            <div class="text-sm">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <div class="ml-auto pl-3">
                <button 
                    type="button" 
                    @click="show = false"
                    class="inline-flex rounded-md p-1.5 hover:bg-black/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-gray-400"
                    aria-label="Dismiss"
                >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>
