@props(['title', 'subtitle' => null])

<div class="mb-8 bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-900">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-gray-600 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ now()->format('h:i A') }}</p>
        </div>
    </div>
</div>