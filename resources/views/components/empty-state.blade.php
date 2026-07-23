@props([
    'title' => 'Nenhum registro encontrado',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'p-12 text-center']) }}>
    <div class="mx-auto w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3 text-slate-400">
        @if(isset($icon))
            {{ $icon }}
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        @endif
    </div>
    <p class="text-slate-500 font-semibold text-sm">{{ $title }}</p>
    @if($description)
        <p class="text-slate-400 text-xs mt-0.5">{{ $description }}</p>
    @endif
    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
