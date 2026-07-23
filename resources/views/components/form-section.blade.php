@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden']) }}>
    @if($title || $description)
        <div class="p-6 border-b border-slate-50">
            @if($title)
                <h3 class="font-bold text-slate-800">{{ $title }}</h3>
            @endif
            @if($description)
                <p class="text-xs text-slate-400 mt-0.5">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @if(isset($actions))
        <div class="p-6 pt-0 border-t border-slate-50 flex justify-end gap-3 mt-6">
            {{ $actions }}
        </div>
    @endif
</div>
