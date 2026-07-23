<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Quarto ' . $room->number" :subtitle="($room->roomType->name ?? 'Tipo não definido') . ' • ' . $room->hotel->name" :backUrl="route('rooms.index')">
            <x-slot name="actions">
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
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Card de Informações Gerais --}}
                <div class="lg:col-span-1 space-y-6">
                    <x-form-section title="Status & Detalhes">
                        <div class="space-y-5">
                            {{-- Badge Status --}}
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Status Atual</span>
                                <x-status-badge :status="$room->status" class="text-sm px-3 py-1.5" />
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
                                    <x-status-badge :status="$room->is_active ? 'active' : 'inactive'" />
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
                    </x-form-section>

                    {{-- Estadia Atual & Próxima --}}
                    <x-form-section title="Ocupação Atual">
                        <div class="space-y-4">
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
                    </x-form-section>
                </div>

                {{-- Histórico de Reservas --}}
                @php
                    $recentReservations = $room->reservations->sortByDesc(fn($res) => $res->pivot->check_in_date)->take(10);
                @endphp
                <div class="lg:col-span-2 space-y-6">
                    <x-table>
                        <x-slot name="header">
                            <h3 class="font-bold text-slate-800">Últimas Ocupações</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Histórico recente de estadias neste quarto</p>
                        </x-slot>

                        <x-slot name="head">
                            <th class="px-6 py-3.5">Localizador</th>
                            <th class="px-6 py-3.5">Hóspede</th>
                            <th class="px-6 py-3.5 text-center">Período</th>
                            <th class="px-6 py-3.5 text-right">Diária</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                        </x-slot>

                        @if($recentReservations->isEmpty())
                            <tr>
                                <td colspan="5">
                                    <x-empty-state title="Sem ocupações registradas" description="Nenhuma reserva utilizou este quarto até o momento.">
                                        <x-slot name="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                        </x-slot>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @else
                            @foreach($recentReservations as $res)
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
                                        <x-status-badge :status="$res->reservation_status === 'canceled' ? 'canceled' : $res->stay_status" />
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </x-table>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
