<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Reservas" subtitle="Gerencie as reservas e estadias do seu hotel">
            <x-slot name="actions">
                <a href="{{ route('reservations.create') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nova Reserva
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <x-flash-message />

            {{-- Filtros --}}
            <form method="GET" action="{{ route('reservations.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[250px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Buscar Reserva</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <input type="text" name="search" value="{{ $search ?? '' }}"
                                   placeholder="Código localizador ou nome do hóspede..."
                                   class="block w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Buscar
                        </button>
                        @if(!empty($search))
                            <a href="{{ route('reservations.index') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl text-sm transition">
                                Limpar
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                <x-table>
                    <x-slot name="header">
                        <h3 class="font-bold text-slate-800">Listagem de Reservas</h3>
                    </x-slot>

                    <x-slot name="head">
                        <th class="px-6 py-3.5">Localizador</th>
                        <th class="px-6 py-3.5">Hóspede</th>
                        <th class="px-6 py-3.5">Check-in</th>
                        <th class="px-6 py-3.5">Check-out</th>
                        <th class="px-6 py-3.5 text-center">Reserva</th>
                        <th class="px-6 py-3.5 text-center">Estadia</th>
                        <th class="px-6 py-3.5 text-right">Valor Total</th>
                        <th class="px-6 py-3.5 text-right">Ações</th>
                    </x-slot>

                    @if($reservations->isEmpty())
                        <tr>
                            <td colspan="8">
                                <x-empty-state title="Nenhuma reserva encontrada" description="Nenhuma reserva ativa foi encontrada ou sua busca não retornou resultados.">
                                    <x-slot name="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                    </x-slot>
                                    <x-slot name="action">
                                        <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition">
                                            Criar Primeira Reserva
                                        </a>
                                    </x-slot>
                                </x-empty-state>
                            </td>
                        </tr>
                    @else
                        @foreach($reservations as $res)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-bold text-indigo-600 whitespace-nowrap">
                                    <a href="{{ route('reservations.show', $res) }}">#{{ $res->locator_code }}</a>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-800">
                                    {{ $res->mainGuest->full_name }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-sm whitespace-nowrap">
                                    {{ $res->check_in_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-sm whitespace-nowrap">
                                    {{ $res->check_out_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <x-status-badge :status="$res->reservation_status" />
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if($res->reservation_status === 'canceled')
                                        <x-status-badge status="canceled" />
                                    @else
                                        <x-status-badge :status="$res->stay_status" />
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-800 whitespace-nowrap">
                                    R$ {{ number_format($res->total_amount, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('reservations.show', $res) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs transition whitespace-nowrap">
                                        Gerenciar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </x-table>

                @if($reservations->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $reservations->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
