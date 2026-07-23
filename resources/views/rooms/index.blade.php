<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Quartos" subtitle="Gerencie os quartos do hotel">
            <x-slot name="actions">
                <a href="{{ route('rooms.create') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Novo Quarto
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            {{-- Filtros --}}
            <form method="GET" action="{{ route('rooms.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Número / Busca</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Ex: 101..."
                                   class="block w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>
                    </div>
                    <div class="min-w-[160px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Status</label>
                        <select name="status" class="block w-full rounded-xl border border-slate-200 text-sm py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">Todos os status</option>
                            <option value="available"    {{ request('status') === 'available'    ? 'selected' : '' }}>Disponível</option>
                            <option value="occupied"     {{ request('status') === 'occupied'     ? 'selected' : '' }}>Ocupado</option>
                            <option value="cleaning"     {{ request('status') === 'cleaning'     ? 'selected' : '' }}>Limpeza</option>
                            <option value="maintenance"  {{ request('status') === 'maintenance'  ? 'selected' : '' }}>Manutenção</option>
                            <option value="blocked"      {{ request('status') === 'blocked'      ? 'selected' : '' }}>Bloqueado</option>
                        </select>
                    </div>
                    @if($roomTypes->count())
                    <div class="min-w-[180px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Tipo</label>
                        <select name="room_type_id" class="block w-full rounded-xl border border-slate-200 text-sm py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">Todos os tipos</option>
                            @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}" {{ request('room_type_id') === $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Filtrar
                        </button>
                        @if(request()->hasAny(['search','status','room_type_id']))
                            <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl text-sm transition">
                                Limpar
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Grid de quartos --}}
            @if($rooms->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <x-empty-state title="Nenhum quarto encontrado" description="Tente ajustar os filtros ou cadastre um novo quarto.">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        </x-slot>
                        <x-slot name="action">
                            <a href="{{ route('rooms.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition">
                                Cadastrar primeiro quarto
                            </a>
                        </x-slot>
                    </x-empty-state>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($rooms as $room)
                        @php
                            $statusConfig = [
                                'available'   => ['bg' => 'bg-emerald-50',  'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500',  'text' => 'text-emerald-700'],
                                'occupied'    => ['bg' => 'bg-indigo-50',   'border' => 'border-indigo-200',  'dot' => 'bg-indigo-500',   'text' => 'text-indigo-700'],
                                'cleaning'    => ['bg' => 'bg-amber-50',    'border' => 'border-amber-200',   'dot' => 'bg-amber-400',    'text' => 'text-amber-700'],
                                'maintenance' => ['bg' => 'bg-orange-50',   'border' => 'border-orange-200',  'dot' => 'bg-orange-500',   'text' => 'text-orange-700'],
                                'blocked'     => ['bg' => 'bg-slate-50',    'border' => 'border-slate-200',   'dot' => 'bg-slate-400',    'text' => 'text-slate-600'],
                            ];
                            $cfg = $statusConfig[$room->status] ?? $statusConfig['blocked'];
                        @endphp
                        <a href="{{ route('rooms.show', $room) }}"
                           class="group relative bg-white rounded-2xl border {{ $cfg['border'] }} shadow-sm hover:shadow-md transition-all duration-200 p-4 flex flex-col items-center gap-2 text-center hover:-translate-y-0.5">

                            {{-- Ícone do quarto --}}
                            <div class="w-12 h-12 rounded-xl {{ $cfg['bg'] }} flex items-center justify-center mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 {{ $cfg['text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                            </div>

                            {{-- Número --}}
                            <div class="font-bold text-slate-800 text-lg leading-tight">{{ $room->number }}</div>

                            {{-- Tipo --}}
                            <div class="text-xs text-slate-500 truncate w-full">{{ $room->roomType->name ?? '—' }}</div>

                            {{-- Status badge --}}
                            <x-status-badge :status="$room->status" />

                            {{-- Andar (se houver) --}}
                            @if($room->floor)
                                <div class="text-[10px] text-slate-400">{{ $room->floor }}º andar</div>
                            @endif
                        </a>
                    @endforeach
                </div>

                {{-- Paginação --}}
                @if($rooms->hasPages())
                    <div class="mt-4">
                        {{ $rooms->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
