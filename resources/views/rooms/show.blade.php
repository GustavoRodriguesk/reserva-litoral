<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('rooms.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">Quarto {{ $room->number }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $room->roomType->name ?? 'Tipo de Quarto não definido' }} • {{ $room->hotel->name }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                {{-- Ações de Bloqueio --}}
                @if($room->status === 'blocked')
                    <form action="{{ route('rooms.unblock', $room) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h16.5a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v13.5a1.5 1.5 0 001.5 1.5z" /></svg>
                            Desbloquear Quarto
                        </button>
                    </form>
                @elseif($room->status !== 'occupied')
                    <form action="{{ route('rooms.block', $room) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm" onclick="return confirm('Bloquear este quarto para manutenção ou reserva externa?');">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                            Bloquear Quarto
                        </button>
                    </form>
                @endif

                <a href="{{ route('rooms.edit', $room) }}"
                   class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                    Editar Quarto
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <span class="font-medium text-sm">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Card de Informações Gerais --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50">
                            <h3 class="font-bold text-slate-800">Status & Detalhes</h3>
                        </div>
                        <div class="p-6 space-y-5">
                            @php
                                $statusConfig = [
                                    'available'   => ['label' => 'Disponível',  'bg' => 'bg-emerald-50',  'border' => 'border-emerald-100', 'dot' => 'bg-emerald-500',  'text' => 'text-emerald-800'],
                                    'occupied'    => ['label' => 'Ocupado',     'bg' => 'bg-indigo-50',   'border' => 'border-indigo-100',  'dot' => 'bg-indigo-500',   'text' => 'text-indigo-800'],
                                    'cleaning'    => ['label' => 'Limpeza',     'bg' => 'bg-amber-50',    'border' => 'border-amber-100',   'dot' => 'bg-amber-400',    'text' => 'text-amber-800'],
                                    'maintenance' => ['label' => 'Manutenção',  'bg' => 'bg-orange-50',   'border' => 'border-orange-100',  'dot' => 'bg-orange-500',   'text' => 'text-orange-800'],
                                    'blocked'     => ['label' => 'Bloqueado',   'bg' => 'bg-slate-50',    'border' => 'border-slate-100',   'dot' => 'bg-slate-400',    'text' => 'text-slate-700'],
                                ];
                                $cfg = $statusConfig[$room->status] ?? $statusConfig['blocked'];
                            @endphp

                            {{-- Badge Status --}}
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Status Atual</span>
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border {{ $cfg['bg'] }} {{ $cfg['border'] }} {{ $cfg['text'] }} font-semibold text-sm">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $cfg['dot'] }}"></span>
                                    {{ $cfg['label'] }}
                                </div>
                            </div>

                            {{-- Informações Simples --}}
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-50">
                                <div>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Número</span>
                                    <span class="font-bold text-slate-800 text-lg">{{ $room->number }}</span>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Andar</span>
                                    <span class="font-bold text-slate-800 text-lg">{{ $room->floor ?? 'Térreo' }}{{ $room->floor ? 'º' : '' }}</span>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Visibilidade</span>
                                    <span class="font-semibold text-sm {{ $room->is_active ? 'text-emerald-600' : 'text-slate-500' }}">
                                        {{ $room->is_active ? 'Ativo (Disponível)' : 'Inativo' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Capacidade</span>
                                    <span class="font-semibold text-slate-800 text-sm">Até {{ $room->roomType->max_capacity ?? '—' }} hóspedes</span>
                                </div>
                            </div>

                            {{-- Detalhes do Tipo --}}
                            @if($room->roomType)
                                <div class="pt-4 border-t border-slate-50 space-y-2">
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Tipo de Quarto</span>
                                    <div class="rounded-xl bg-slate-50 p-3.5 space-y-1">
                                        <p class="font-bold text-slate-800 text-sm">{{ $room->roomType->name }}</p>
                                        <p class="text-xs text-slate-500 leading-relaxed">{{ $room->roomType->description ?? 'Sem descrição adicional' }}</p>
                                        <p class="text-xs font-semibold text-indigo-600 mt-2 block">Tarifa base: R$ {{ number_format($room->roomType->base_price, 2, ',', '.') }}/noite</p>
                                    </div>
                                </div>
                            @endif


                        </div>
                    </div>

                    {{-- Estadia Atual & Próxima --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50">
                            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider text-slate-500">Ocupação Atual</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Hóspede Hospedado</span>
                                @if($currentReservation)
                                    <div class="p-3.5 bg-indigo-50 border border-indigo-100 rounded-xl">
                                        <p class="font-bold text-indigo-950 text-sm">{{ $currentReservation->guest->full_name }}</p>
                                        <p class="text-xs text-indigo-700 mt-1">Reserva: <a href="{{ route('reservations.show', $currentReservation->id) }}" class="underline font-semibold">#{{ $currentReservation->locator_code }}</a></p>
                                    </div>
                                @else
                                    <p class="text-slate-500 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">Nenhum hóspede atualmente no quarto.</p>
                                @endif
                            </div>

                            <div class="pt-4 border-t border-slate-50">
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Próxima Reserva</span>
                                @if($nextReservation)
                                    <div class="p-3.5 bg-sky-50 border border-sky-100 rounded-xl">
                                        <p class="font-bold text-sky-950 text-sm">{{ $nextReservation->guest->full_name }}</p>
                                        <p class="text-xs text-sky-700 mt-1">Check-in: <strong>{{ \Carbon\Carbon::parse($nextReservation->check_in)->format('d/m/Y') }}</strong></p>
                                        <p class="text-xs text-sky-700 mt-0.5">Reserva: <a href="{{ route('reservations.show', $nextReservation->id) }}" class="underline font-semibold">#{{ $nextReservation->locator_code }}</a></p>
                                    </div>
                                @else
                                    <p class="text-slate-500 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">Nenhuma reserva futura agendada.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Histórico de Reservas --}}
                @php
                    $recentReservations = $room->reservations->sortByDesc(fn($res) => $res->pivot->check_in_date)->take(10);
                @endphp
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50">
                            <h3 class="font-bold text-slate-800">Últimas Ocupações</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Histórico recente de estadias neste quarto</p>
                        </div>
                        <div class="overflow-x-auto">
                            @if($recentReservations->isEmpty())
                                <div class="p-12 text-center">
                                    <div class="mx-auto w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                    </div>
                                    <p class="text-slate-500 font-semibold text-sm">Sem ocupações registradas</p>
                                    <p class="text-slate-400 text-xs mt-0.5">Nenhuma reserva utilizou este quarto até o momento.</p>
                                </div>
                            @else
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                            <th class="px-6 py-3.5">Localizador</th>
                                            <th class="px-6 py-3.5">Hóspede</th>
                                            <th class="px-6 py-3.5 text-center">Período</th>
                                            <th class="px-6 py-3.5 text-right">Diária</th>
                                            <th class="px-6 py-3.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-sm">
                                        @foreach($recentReservations as $res)
                                            @php
                                                $resColors = [
                                                    'awaiting_checkin' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                    'checked_in'       => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                                    'checked_out'      => 'bg-purple-50 text-purple-700 border-purple-100',
                                                ];
                                                $resLabels = [
                                                    'awaiting_checkin' => 'Aguardando Check-in',
                                                    'checked_in'       => 'Hospedado',
                                                    'checked_out'      => 'Finalizada',
                                                ];
                                                $st = $res->stay_status;
                                                // Se estiver cancelada
                                                if ($res->reservation_status === 'canceled') {
                                                    $stColor = 'bg-rose-50 text-rose-700 border-rose-100';
                                                    $stLabel = 'Cancelada';
                                                } else {
                                                    $stColor = $resColors[$st] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                                                    $stLabel = $resLabels[$st] ?? $st;
                                                }
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 transition">
                                                <td class="px-6 py-4 font-bold text-indigo-600 whitespace-nowrap">
                                                    <a href="{{ route('reservations.show', $res->id) }}">
                                                        #{{ $res->locator_code }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-800 truncate max-w-[150px]">
                                                    {{ $res->guest->full_name ?? 'Hóspede Não Informado' }}
                                                </td>
                                                <td class="px-6 py-4 text-center text-slate-500 whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($res->pivot->check_in_date)->format('d/m') }} 
                                                    → 
                                                    {{ \Carbon\Carbon::parse($res->pivot->check_out_date)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 text-right font-semibold text-slate-800 whitespace-nowrap">
                                                    R$ {{ number_format($res->pivot->rate_per_night, 2, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold border rounded-full {{ $stColor }}">
                                                        {{ $stLabel }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
