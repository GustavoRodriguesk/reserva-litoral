<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Painel de Controle</h2>
                <p class="text-sm text-gray-500 mt-1">Visão geral das operações e ocupação de hoje</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reservations.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nova Reserva
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- 1. Cards de Métricas no Topo --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Receita --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.83-1.106-2.17 0-3 1.015-.761 2.685-.761 3.7 0L14.25 9M12 3v3m0 12v3" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Receita Hoje</p>
                        <p class="font-bold text-slate-800 text-2xl mt-0.5">R$ {{ number_format($revenueToday, 2, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Ocupação --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ocupação Atual</p>
                        <p class="font-bold text-slate-800 text-2xl mt-0.5">{{ $occupancy }}%</p>
                    </div>
                </div>

                {{-- Check-ins --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Check-ins Hoje</p>
                        <p class="font-bold text-slate-800 text-2xl mt-0.5">{{ $checkins }}</p>
                    </div>
                </div>

                {{-- Check-outs --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Check-outs Hoje</p>
                        <p class="font-bold text-slate-800 text-2xl mt-0.5">{{ $checkouts }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Coluna 2/3 - Quartos e Reservas --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Status Operacional dos Quartos --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <div class="flex justify-between items-center mb-5">
                            <div>
                                <h3 class="font-bold text-slate-800">Status dos Quartos</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Clique em um status para filtrar os quartos</p>
                            </div>
                            <a href="{{ route('rooms.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">Ver todos os quartos →</a>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            {{-- Disponível --}}
                            <a href="{{ route('rooms.index', ['status' => 'available']) }}" class="p-4 bg-emerald-50/50 border border-emerald-100 hover:bg-emerald-50 rounded-xl text-center transition group">
                                <span class="block text-2xl font-bold text-emerald-600">{{ $roomStatus['available'] }}</span>
                                <span class="block text-xs font-medium text-emerald-800 mt-1">Disponíveis</span>
                            </a>
                            {{-- Ocupado --}}
                            <a href="{{ route('rooms.index', ['status' => 'occupied']) }}" class="p-4 bg-indigo-50/50 border border-indigo-100 hover:bg-indigo-50 rounded-xl text-center transition group">
                                <span class="block text-2xl font-bold text-indigo-600">{{ $roomStatus['occupied'] }}</span>
                                <span class="block text-xs font-medium text-indigo-800 mt-1">Ocupados</span>
                            </a>
                            {{-- Sujo/Limpeza --}}
                            <a href="{{ route('rooms.index', ['status' => 'cleaning']) }}" class="p-4 bg-amber-50/50 border border-amber-100 hover:bg-amber-50 rounded-xl text-center transition group">
                                <span class="block text-2xl font-bold text-amber-600">{{ $roomStatus['cleaning'] }}</span>
                                <span class="block text-xs font-medium text-amber-800 mt-1">Sujos</span>
                            </a>
                            {{-- Manutenção --}}
                            <a href="{{ route('rooms.index', ['status' => 'maintenance']) }}" class="p-4 bg-orange-50/50 border border-orange-100 hover:bg-orange-50 rounded-xl text-center transition group">
                                <span class="block text-2xl font-bold text-orange-600">{{ $roomStatus['maintenance'] }}</span>
                                <span class="block text-xs font-medium text-orange-800 mt-1">Manutenção</span>
                            </a>
                            {{-- Bloqueado --}}
                            <a href="{{ route('rooms.index', ['status' => 'blocked']) }}" class="p-4 bg-slate-50 border border-slate-100 hover:bg-slate-100 rounded-xl text-center transition group">
                                <span class="block text-2xl font-bold text-slate-600">{{ $roomStatus['blocked'] }}</span>
                                <span class="block text-xs font-medium text-slate-800 mt-1">Bloqueados</span>
                            </a>
                        </div>
                    </div>

                    {{-- Rack de Quartos --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <div class="mb-5">
                            <h3 class="font-bold text-slate-800">Mapa Operacional de Quartos (Rack)</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Clique em um quarto para abrir seus detalhes, gerenciar reservas, editar ou excluir</p>
                        </div>

                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                            @foreach($roomsList as $rm)
                                @php
                                    $statusConfig = [
                                        'available'   => ['bg' => 'bg-emerald-50/50 hover:bg-emerald-50',  'border' => 'border-emerald-100 hover:border-emerald-200', 'dot' => 'bg-emerald-500',  'text' => 'text-emerald-800', 'label' => 'Disponível'],
                                        'occupied'    => ['bg' => 'bg-indigo-50/50 hover:bg-indigo-50',   'border' => 'border-indigo-100 hover:border-indigo-200',  'dot' => 'bg-indigo-500',   'text' => 'text-indigo-800', 'label' => 'Ocupado'],
                                        'cleaning'    => ['bg' => 'bg-amber-50/50 hover:bg-amber-50',    'border' => 'border-amber-100 hover:border-amber-200',   'dot' => 'bg-amber-400',    'text' => 'text-amber-800', 'label' => 'Sujo'],
                                        'maintenance' => ['bg' => 'bg-orange-50/50 hover:bg-orange-50',   'border' => 'border-orange-100 hover:border-orange-200',  'dot' => 'bg-orange-500',   'text' => 'text-orange-800', 'label' => 'Manutenção'],
                                        'blocked'     => ['bg' => 'bg-slate-50 hover:bg-slate-100',       'border' => 'border-slate-100 hover:border-slate-200',    'dot' => 'bg-slate-400',    'text' => 'text-slate-700', 'label' => 'Bloqueado'],
                                    ];
                                    $cfg = $statusConfig[$rm->status] ?? $statusConfig['blocked'];
                                @endphp
                                <a href="{{ route('rooms.show', $rm->id) }}" class="p-3 bg-white border {{ $cfg['border'] }} {{ $cfg['bg'] }} rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center hover:-translate-y-0.5 shadow-sm hover:shadow-md">
                                    <div class="font-bold text-slate-800 text-base leading-none">{{ $rm->number }}</div>
                                    <div class="text-[9px] text-slate-400 truncate w-full leading-none">{{ $rm->type_name }}</div>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                                        <span class="text-[8px] font-semibold uppercase tracking-wider {{ $cfg['text'] }}">{{ $cfg['label'] }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Últimas Reservas --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-slate-800">Últimas Reservas Criadas</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Reservas registradas recentemente no sistema</p>
                            </div>
                            <a href="{{ route('reservations.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">Ver todas as reservas →</a>
                        </div>

                        <div class="overflow-x-auto">
                            @if(empty($recentReservations))
                                <div class="p-12 text-center">
                                    <div class="mx-auto w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                    </div>
                                    <p class="text-slate-500 font-semibold text-sm">Nenhuma reserva cadastrada</p>
                                    <p class="text-slate-400 text-xs mt-0.5">Clique em "Nova Reserva" para cadastrar a primeira.</p>
                                </div>
                            @else
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                            <th class="px-6 py-3.5">Localizador</th>
                                            <th class="px-6 py-3.5">Hóspede</th>
                                            <th class="px-6 py-3.5 text-center">Check-in</th>
                                            <th class="px-6 py-3.5 text-center">Check-out</th>
                                            <th class="px-6 py-3.5 text-right">Total</th>
                                            <th class="px-6 py-3.5 text-center">Estadia</th>
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
                                                    {{ $res->guest_name ?? 'Hóspede Não Informado' }}
                                                </td>
                                                <td class="px-6 py-4 text-center text-slate-500 whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($res->check_in_date)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 text-center text-slate-500 whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($res->check_out_date)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 text-right font-semibold text-slate-800 whitespace-nowrap">
                                                    R$ {{ number_format($res->total_amount, 2, ',', '.') }}
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

                {{-- Coluna 1/3 - Ações e Estatísticas Auxiliares --}}
                <div class="space-y-6">

                    {{-- Card de Ações Rápidas --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <h3 class="font-bold text-slate-800">Acesso Rápido</h3>
                        <div class="grid grid-cols-1 gap-2">
                            <a href="{{ route('planning.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-indigo-50/50 hover:border-indigo-100 transition group text-slate-700">
                                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-100 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-semibold text-slate-800 group-hover:text-indigo-900 transition">Mapa de Ocupação</span>
                                    <span class="block text-[10px] text-slate-400">Ver e arrastar reservas no calendário</span>
                                </div>
                            </a>

                            <a href="{{ route('rooms.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-sky-50/50 hover:border-sky-100 transition group text-slate-700">
                                <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 group-hover:bg-sky-100 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-semibold text-slate-800 group-hover:text-sky-900 transition">Gerenciar Quartos</span>
                                    <span class="block text-[10px] text-slate-400">Criar, editar e bloquear quartos</span>
                                </div>
                            </a>

                            <a href="{{ route('guests.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-emerald-50/50 hover:border-emerald-100 transition group text-slate-700">
                                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-semibold text-slate-800 group-hover:text-emerald-900 transition">Ficha de Hóspedes</span>
                                    <span class="block text-[10px] text-slate-400">Gerenciar hóspedes e fidelidade</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    {{-- Alertas Rápidos de Governança --}}
                    @php
                        $dirtyRoomsCount = $roomStatus['cleaning'];
                        $maintenanceRoomsCount = $roomStatus['maintenance'];
                    @endphp
                    @if($dirtyRoomsCount > 0 || $maintenanceRoomsCount > 0)
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                            <h3 class="font-bold text-slate-800">Alertas de Governança</h3>
                            <div class="space-y-2">
                                @if($dirtyRoomsCount > 0)
                                    <a href="{{ route('rooms.index', ['status' => 'cleaning']) }}" class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl hover:bg-amber-100/50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                        <div>
                                            <span class="block text-xs font-bold text-amber-800">Quartos Sujos ({{ $dirtyRoomsCount }})</span>
                                            <span class="block text-[10px] text-amber-600 mt-0.5">Exige limpeza para liberação de novos hóspedes.</span>
                                        </div>
                                    </a>
                                @endif

                                @if($maintenanceRoomsCount > 0)
                                    <a href="{{ route('rooms.index', ['status' => 'maintenance']) }}" class="flex items-start gap-3 p-3 bg-orange-50 border border-orange-100 rounded-xl hover:bg-orange-100/50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-orange-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.075a2.001 2.001 0 01-1.896-1.896c0-1.285.836-2.403 2.065-2.722a12.003 12.003 0 013.235 0c1.229.319 2.065 1.437 2.065 2.722a2.001 2.001 0 01-1.896 1.896L12 15.075zM12 15v3.75m-6.303-3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                        <div>
                                            <span class="block text-xs font-bold text-orange-800">Bloqueio de Manutenção ({{ $maintenanceRoomsCount }})</span>
                                            <span class="block text-[10px] text-orange-600 mt-0.5">Quartos temporariamente indisponíveis no sistema.</span>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>