@props([
    'headers' => [],
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden']) }}>
    @if(isset($header))
        <div class="p-6 border-b border-slate-50">
            {{ $header }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            @if(!empty($headers) || isset($head))
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                        @if(isset($head))
                            {{ $head }}
                        @else
                            @foreach($headers as $h)
                                <th class="px-6 py-3.5">{{ $h }}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-slate-50 text-sm">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($footer))
        <div class="p-4 border-t border-slate-50">
            {{ $footer }}
        </div>
    @endif
</div>
