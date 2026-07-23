@props([
    'title' => null,
    'subtitle' => null,
    'backUrl' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row justify-between items-start md:items-center gap-4']) }}>
    <div class="flex items-center gap-4">
        @if($backUrl)
            <a href="{{ $backUrl }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
        @endif
        <div>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ $title ?? $slot }}
            </h2>
            @if($subtitle)
                <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(isset($actions))
        <div class="flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
