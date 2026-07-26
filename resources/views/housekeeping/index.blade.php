<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Governança & Limpeza" subtitle="Gerencie o ciclo de limpeza e manutenção dos quartos">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <a href="{{ route('rooms.index') }}"
                       class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl text-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        Ver Todos os Quartos
                    </a>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            {{-- Métricas / Contadores rápidos --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <a href="{{ route('housekeeping.index', ['status' => 'cleaning']) }}"
                   class="bg-white p-4 rounded-2xl border {{ request('status') === 'cleaning' ? 'border-amber-500 ring-2 ring-amber-200' : 'border-slate-100' }} shadow-sm hover:shadow-md transition">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Limpeza</div>
                    <div class="text-2xl font-black text-amber-600 mt-1">{{ $counts['cleaning'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Quartos sujos</div>
                </a>

                <a href="{{ route('housekeeping.index', ['status' => 'inspected']) }}"
                   class="bg-white p-4 rounded-2xl border {{ request('status') === 'inspected' ? 'border-teal-500 ring-2 ring-teal-200' : 'border-slate-100' }} shadow-sm hover:shadow-md transition">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Inspecionado</div>
                    <div class="text-2xl font-black text-teal-600 mt-1">{{ $counts['inspected'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Aguardando liberação</div>
                </a>

                <a href="{{ route('housekeeping.index', ['status' => 'available']) }}"
                   class="bg-white p-4 rounded-2xl border {{ request('status') === 'available' ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-slate-100' }} shadow-sm hover:shadow-md transition">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Livre</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $counts['available'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Pronto p/ entrada</div>
                </a>

                <a href="{{ route('housekeeping.index', ['status' => 'reserved']) }}"
                   class="bg-white p-4 rounded-2xl border {{ request('status') === 'reserved' ? 'border-sky-500 ring-2 ring-sky-200' : 'border-slate-100' }} shadow-sm hover:shadow-md transition">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Reservado</div>
                    <div class="text-2xl font-black text-sky-600 mt-1">{{ $counts['reserved'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Entrada hoje</div>
                </a>

                <a href="{{ route('housekeeping.index', ['status' => 'occupied']) }}"
                   class="bg-white p-4 rounded-2xl border {{ request('status') === 'occupied' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-slate-100' }} shadow-sm hover:shadow-md transition">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Ocupado</div>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ $counts['occupied'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Hóspedes no hotel</div>
                </a>

                <a href="{{ route('housekeeping.index') }}"
                   class="bg-white p-4 rounded-2xl border {{ !request('status') ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-slate-100' }} shadow-sm hover:shadow-md transition">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Todos</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $counts['total'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Total de quartos</div>
                </a>
            </div>

            {{-- Filtros Rápidos --}}
            <form method="GET" action="{{ route('housekeeping.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Filtrar:</span>
                    <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-200 text-sm py-1.5 px-3 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos os status</option>
                        <option value="cleaning" {{ request('status') === 'cleaning' ? 'selected' : '' }}>Limpeza</option>
                        <option value="inspected" {{ request('status') === 'inspected' ? 'selected' : '' }}>Inspecionado</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Livre</option>
                        <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reservado</option>
                        <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Ocupado</option>
                        <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Manutenção</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>

                    <select name="room_type_id" onchange="this.form.submit()" class="rounded-xl border border-slate-200 text-sm py-1.5 px-3 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos os tipos de acomodação</option>
                        @foreach($roomTypes as $rt)
                            <option value="{{ $rt->id }}" {{ request('room_type_id') === $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(request()->hasAny(['status', 'room_type_id']))
                    <a href="{{ route('housekeeping.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Limpar filtros</a>
                @endif
            </form>

            {{-- Grid de Governança dos Quartos --}}
            @if($rooms->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-100 p-8 shadow-sm">
                    <x-empty-state title="Nenhum quarto encontrado" description="Nenhum quarto atende aos critérios de filtro aplicados." />
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($rooms as $room)
                        @php
                            $cardColors = [
                                'available'   => 'border-emerald-200 bg-emerald-50/20',
                                'reserved'    => 'border-sky-200 bg-sky-50/20',
                                'occupied'    => 'border-indigo-200 bg-indigo-50/20',
                                'cleaning'    => 'border-amber-300 bg-amber-50/40 ring-1 ring-amber-300',
                                'inspected'   => 'border-teal-300 bg-teal-50/40 ring-1 ring-teal-300',
                                'maintenance' => 'border-orange-200 bg-orange-50/20',
                                'blocked'     => 'border-slate-200 bg-slate-50/20',
                            ];
                            $cardStyle = $cardColors[$room->status] ?? 'border-slate-200 bg-white';
                        @endphp
                        
                        <div class="bg-white rounded-2xl border {{ $cardStyle }} shadow-sm p-5 flex flex-col justify-between transition hover:shadow-md">
                            <div>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-2xl font-black text-slate-800">Quarto {{ $room->number }}</div>
                                        <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $room->roomType->name ?? '—' }} @if($room->floor) • {{ $room->floor }}º andar @endif</div>
                                    </div>
                                    <x-status-badge :status="$room->status" />
                                </div>

                                {{-- Detalhes do status --}}
                                <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-600 space-y-1">
                                    @if($room->status === 'cleaning')
                                        <p class="text-amber-800 font-medium flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                            Aguardando limpeza e arrumação de enxoval.
                                        </p>
                                    @elseif($room->status === 'inspected')
                                        <p class="text-teal-800 font-medium flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                            Quarto limpo! Aguardando vistoria final para liberar.
                                        </p>
                                    @elseif($room->status === 'available')
                                        <p class="text-emerald-800 font-medium flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            Livre e pronto para nova hospedagem.
                                        </p>
                                    @elseif($room->status === 'reserved')
                                        <p class="text-sky-800 font-medium flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                            Reservado com check-in pendente.
                                        </p>
                                    @elseif($room->status === 'occupied')
                                        <p class="text-indigo-800 font-medium flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                            Hóspede em permanência.
                                        </p>
                                    @else
                                        <p class="text-slate-500">Quarto fora de operação.</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Ações Rápidas do Fluxo de Governança --}}
                            <div class="mt-5 pt-3 border-t border-slate-100 flex flex-col gap-2">
                                {{-- Ação Principal Baseada no Fluxo Solicitado --}}
                                @if($room->status === 'cleaning')
                                    <form action="{{ route('housekeeping.update-status', $room) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="inspected">
                                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-3 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                            Concluir Limpeza ➔ Inspecionado
                                        </button>
                                    </form>
                                @elseif($room->status === 'inspected')
                                    <form action="{{ route('housekeeping.update-status', $room) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="available">
                                        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-3 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Aprovar Inspeção ➔ Liberar Quarto
                                        </button>
                                    </form>
                                @elseif($room->status === 'available')
                                    <div class="flex gap-2">
                                        <form action="{{ route('housekeeping.update-status', $room) }}" method="POST" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="reserved">
                                            <button type="submit" class="w-full bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 font-semibold py-1.5 px-2 rounded-xl text-xs transition">
                                                ➔ Reservado
                                            </button>
                                        </form>
                                        <form action="{{ route('housekeeping.update-status', $room) }}" method="POST" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cleaning">
                                            <button type="submit" class="w-full bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-semibold py-1.5 px-2 rounded-xl text-xs transition">
                                                ➔ Limpeza
                                            </button>
                                        </form>
                                    </div>
                                @elseif($room->status === 'reserved')
                                    <form action="{{ route('housekeeping.update-status', $room) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="occupied">
                                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                            Entrada Hóspede ➔ Ocupado
                                        </button>
                                    </form>
                                @elseif($room->status === 'occupied')
                                    <form action="{{ route('housekeeping.update-status', $room) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cleaning">
                                        <button type="submit" class="w-full bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold py-2 px-3 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                                            Saída Hóspede ➔ Sujo / Limpeza
                                        </button>
                                    </form>
                                @endif

                                {{-- Seletor de Estado Secundário / Manual --}}
                                <div class="relative group/select">
                                    <form action="{{ route('housekeeping.update-status', $room) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 text-[11px] py-1 px-2 text-slate-500 bg-slate-50 focus:bg-white focus:border-indigo-500">
                                            <option value="" disabled selected>Alterar status manualmente...</option>
                                            <option value="available">Definir como Livre</option>
                                            <option value="reserved">Definir como Reservado</option>
                                            <option value="occupied">Definir como Ocupado</option>
                                            <option value="cleaning">Definir como Limpeza</option>
                                            <option value="inspected">Definir como Inspecionado</option>
                                            <option value="maintenance">Definir como Manutenção</option>
                                            <option value="blocked">Definir como Bloqueado</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
