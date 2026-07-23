@props([
    'label',
    'value',
    'color' => 'indigo',
])

@php
    $colors = [
        'indigo'  => 'bg-indigo-50 text-indigo-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'blue'    => 'bg-blue-50 text-blue-600',
        'purple'  => 'bg-purple-50 text-purple-600',
        'amber'   => 'bg-amber-50 text-amber-600',
        'orange'  => 'bg-orange-50 text-orange-600',
        'rose'    => 'bg-rose-50 text-rose-600',
        'slate'   => 'bg-slate-50 text-slate-600',
    ];
    $iconBg = $colors[$color] ?? $colors['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-center gap-4']) }}>
    @if(isset($icon))
        <div class="w-12 h-12 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
            {{ $icon }}
        </div>
    @endif
    <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ $label }}</p>
        <p class="font-bold text-slate-800 text-2xl mt-0.5">{{ $value }}</p>
        @if(isset($subtext))
            <p class="text-xs text-slate-500 mt-1">{{ $subtext }}</p>
        @endif
    </div>
</div>
