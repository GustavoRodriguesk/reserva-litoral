<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="$guest->full_name"
            :subtitle="'Hóspede cadastrado em ' . $guest->created_at->format('d/m/Y')"
            :backUrl="route('guests.index')">
            <x-slot name="actions">
                <a href="{{ route('guests.edit', $guest) }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                    Editar Hóspede
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            {{-- Métricas do Hóspede --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Gasto</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">R$ {{ number_format($totalSpent, 2, ',', '.') }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">em todas as estadias</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Estadias Realizadas</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1">{{ $totalStays }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">check-outs concluídos</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Próximas Reservas</p>
                    <p class="text-2xl font-black text-sky-600 mt-1">{{ $upcomingCount }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">confirmadas e pendentes</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Última Visita</p>
                    <p class="text-2xl font-black text-slate-800 mt-1">
                        {{ $lastStay ? $lastStay->check_out_date->format('d/m/Y') : '—' }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5">data do último check-out</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Coluna Esquerda: Dados do Hóspede --}}
                <div class="space-y-5">

                    {{-- Dados Pessoais --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">
                            Dados Pessoais
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-start">
                                <span class="text-slate-500">Nome completo</span>
                                <span class="font-semibold text-slate-800 text-right ml-4">{{ $guest->full_name }}</span>
                            </div>
                            @if($guest->email)
                            <div class="flex justify-between items-start">
                                <span class="text-slate-500">E-mail</span>
                                <a href="mailto:{{ $guest->email }}" class="font-semibold text-indigo-600 hover:underline text-right ml-4">{{ $guest->email }}</a>
                            </div>
                            @endif
                            @if($guest->phone)
                            <div class="flex justify-between items-start">
                                <span class="text-slate-500">Telefone</span>
                                <a href="tel:{{ $guest->phone }}" class="font-semibold text-slate-800 text-right ml-4">{{ $guest->phone }}</a>
                            </div>
                            @endif
                            @if($guest->document_number)
                            <div class="flex justify-between items-start">
                                <span class="text-slate-500">{{ strtoupper($guest->document_type ?? 'CPF') }}</span>
                                <span class="font-semibold text-slate-800 text-right ml-4">{{ $guest->document_number }}</span>
                            </div>
                            @endif
                            @if($guest->birth_date)
                            <div class="flex justify-between items-start">
                                <span class="text-slate-500">Data de Nascimento</span>
                                <span class="font-semibold text-slate-800 text-right ml-4">
                                    {{ $guest->birth_date->format('d/m/Y') }}
                                    <span class="text-slate-400 text-xs">({{ $guest->birth_date->age }} anos)</span>
                                </span>
                            </div>
                            @endif
                            @if($guest->nationality)
                            <div class="flex justify-between items-start">
                                <span class="text-slate-500">Nacionalidade</span>
                                <span class="font-semibold text-slate-800 text-right ml-4">{{ $guest->nationality }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Observações / Preferências --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 pb-3 border-b border-slate-100">
                            Observações & Preferências
                        </h3>
                        @if($guest->notes)
                            <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $guest->notes }}</p>
                        @else
                            <p class="text-sm text-slate-400 italic">Nenhuma observação registrada.</p>
                        @endif
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <a href="{{ route('guests.edit', $guest) }}"
                               class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                + Adicionar observações
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Coluna Direita: Histórico de Reservas --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                        <div class="flex justify-between items-center p-6 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                                Histórico de Reservas
                            </h3>
                            <a href="{{ route('reservations.create') }}?guest_id={{ $guest->id }}"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Nova Reserva
                            </a>
                        </div>

                        @if($reservations->isEmpty())
                            <div class="p-10">
                                <x-empty-state
                                    title="Nenhuma reserva encontrada"
                                    description="Este hóspede ainda não realizou nenhuma reserva."
                                />
                            </div>
                        @else
                            <div class="divide-y divide-slate-100">
                                @foreach($reservations as $reservation)
                                    @php
                                        $nights = $reservation->check_in_date->diffInDays($reservation->check_out_date);
                                        $room   = $reservation->rooms->first();
                                    @endphp
                                    <a href="{{ route('reservations.show', $reservation) }}"
                                       class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition group">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-indigo-50 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-500 group-hover:text-indigo-600 transition" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-700 transition">
                                                    #{{ $reservation->locator_code }}
                                                    @if($room) <span class="font-normal text-slate-500">· Qto {{ $room->number }}</span> @endif
                                                </p>
                                                <p class="text-xs text-slate-500 mt-0.5">
                                                    {{ $reservation->check_in_date->format('d/m/Y') }} → {{ $reservation->check_out_date->format('d/m/Y') }}
                                                    &nbsp;·&nbsp; {{ $nights }} {{ $nights == 1 ? 'noite' : 'noites' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-slate-800">R$ {{ number_format($reservation->total_amount, 2, ',', '.') }}</p>
                                                <x-status-badge :status="$reservation->stay_status" class="mt-0.5" />
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-300 group-hover:text-indigo-400 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
