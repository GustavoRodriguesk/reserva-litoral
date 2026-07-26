<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="$type === 'financial' ? 'Relatório Financeiro' : 'Relatório de Ocupação'"
            subtitle="Análise do período selecionado">
            <x-slot name="actions">
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.75 19.5m10.56-5.671-.721 5.671M6.75 19.5l-1.5-1.5M17.25 19.5l1.5-1.5M6.75 4.5h10.5M5.25 6.75h13.5" /></svg>
                    Imprimir
                </button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            {{-- Filtros --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tipo de Relatório</label>
                        <select name="type" class="rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm min-w-[200px]">
                            <option value="occupancy" {{ $type === 'occupancy' ? 'selected' : '' }}>Ocupação & Performance</option>
                            <option value="financial" {{ $type === 'financial' ? 'selected' : '' }}>Financeiro & Receita</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Data Início</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Data Fim</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803 7.5 7.5 0 0 0 15.803 15.803Z" /></svg>
                        Gerar Relatório
                    </button>
                    {{-- Quick links --}}
                    <div class="flex gap-2 ml-auto">
                        @php
                            $quickRanges = [
                                'Hoje'        => [now()->toDateString(), now()->toDateString()],
                                'Este Mês'    => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                                'Mês Passado' => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                                'Este Ano'    => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
                            ];
                        @endphp
                        @foreach($quickRanges as $label => [$s, $e])
                            <a href="{{ route('reports.index', ['type' => $type, 'start_date' => $s, 'end_date' => $e]) }}"
                               class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 px-2 py-1 rounded-lg hover:bg-indigo-50 transition">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </form>
            </div>

            @if($type === 'occupancy')
                {{-- ============================================================ --}}
                {{-- RELATÓRIO DE OCUPAÇÃO --}}
                {{-- ============================================================ --}}

                {{-- KPIs principais --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Taxa de Ocupação</p>
                        <p class="text-3xl font-black mt-1 {{ $occupancyRate >= 70 ? 'text-emerald-600' : ($occupancyRate >= 40 ? 'text-amber-500' : 'text-rose-500') }}">
                            {{ number_format($occupancyRate, 1, ',', '.') }}%
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ number_format($occupiedNights, 0, ',', '.') }} / {{ number_format($totalRoomNights, 0, ',', '.') }} noites/quarto</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">ADR</p>
                        <p class="text-3xl font-black text-indigo-600 mt-1">R$ {{ number_format($adr, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Diária média por quarto ocupado</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">RevPAR</p>
                        <p class="text-3xl font-black text-sky-600 mt-1">R$ {{ number_format($revpar, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Receita por quarto disponível</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Receita Total</p>
                        <p class="text-3xl font-black text-emerald-600 mt-1">R$ {{ number_format($revenue, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $totalRooms }} quartos no período</p>
                    </div>
                </div>

                {{-- Por Tipo de Quarto --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Desempenho por Tipo de Quarto</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold">Tipo</th>
                                    <th class="px-6 py-3 text-center font-semibold">Quartos</th>
                                    <th class="px-6 py-3 text-center font-semibold">Reservas</th>
                                    <th class="px-6 py-3 text-right font-semibold">Receita</th>
                                    <th class="px-6 py-3 text-right font-semibold">Média/Reserva</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($byRoomType as $rt)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-3 font-semibold text-slate-800">{{ $rt->name }}</td>
                                        <td class="px-6 py-3 text-center text-slate-600">{{ $rt->total_rooms }}</td>
                                        <td class="px-6 py-3 text-center text-slate-600">{{ $rt->reservations }}</td>
                                        <td class="px-6 py-3 text-right font-semibold text-slate-800">R$ {{ number_format($rt->revenue, 2, ',', '.') }}</td>
                                        <td class="px-6 py-3 text-right text-slate-600">
                                            R$ {{ $rt->reservations > 0 ? number_format($rt->revenue / $rt->reservations, 2, ',', '.') : '0,00' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">Nenhum dado no período selecionado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                {{-- ============================================================ --}}
                {{-- RELATÓRIO FINANCEIRO --}}
                {{-- ============================================================ --}}

                {{-- KPIs financeiros --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Receita de Diárias</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">R$ {{ number_format($reservationRevenue, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">total das reservas no período</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Extras & Consumo</p>
                        <p class="text-2xl font-black text-indigo-600 mt-1">R$ {{ number_format($extraCharges, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">cobranças adicionais</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pagamentos Recebidos</p>
                        <p class="text-2xl font-black text-sky-600 mt-1">R$ {{ number_format($paymentsReceived, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">confirmados no período</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Saldo a Receber</p>
                        <p class="text-2xl font-black {{ $pendingBalance > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">
                            R$ {{ number_format($pendingBalance, 2, ',', '.') }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">receita - pagamentos</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Por Método de Pagamento --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                        <div class="px-6 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Por Método de Pagamento</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @forelse($byPaymentMethod as $pm)
                                @php
                                    $pct = $paymentsReceived > 0 ? ($pm->total / $paymentsReceived) * 100 : 0;
                                    $labels = ['pix' => 'PIX', 'credit_card' => 'Cartão de Crédito', 'debit_card' => 'Cartão de Débito', 'cash' => 'Dinheiro', 'bank_transfer' => 'Transferência'];
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-semibold text-slate-700">{{ $labels[$pm->method] ?? ucfirst($pm->method) }}</span>
                                        <span class="text-slate-600">R$ {{ number_format($pm->total, 2, ',', '.') }} <span class="text-slate-400 text-xs">({{ number_format($pct, 0) }}%)</span></span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400 italic py-4 text-center">Nenhum pagamento no período.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Tendência Mensal --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                        <div class="px-6 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Tendência dos Últimos 6 Meses</h3>
                        </div>
                        <div class="p-6">
                            @if($monthlyTrend->isNotEmpty())
                                @php $maxRevenue = $monthlyTrend->max('revenue') ?: 1; @endphp
                                <div class="space-y-3">
                                    @foreach($monthlyTrend as $month)
                                        @php
                                            $pct = ($month->revenue / $maxRevenue) * 100;
                                            $label = \Carbon\Carbon::createFromFormat('Y-m', $month->month)->locale('pt_BR')->isoFormat('MMM/YY');
                                        @endphp
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="font-semibold text-slate-700 capitalize">{{ $label }}</span>
                                                <span class="text-slate-600">R$ {{ number_format($month->revenue, 2, ',', '.') }} <span class="text-slate-400 text-xs">({{ $month->count }} res.)</span></span>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                                <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: {{ min($pct, 100) }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-400 italic py-4 text-center">Nenhuma reserva nos últimos 6 meses.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status das Reservas --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Status das Reservas no Período</h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-slate-100">
                        @php
                            $statusLabels = [
                                'confirmed'  => ['Confirmadas', 'text-emerald-600', 'bg-emerald-50'],
                                'pending'    => ['Pendentes',   'text-amber-600',   'bg-amber-50'],
                                'cancelled'  => ['Canceladas',  'text-rose-600',    'bg-rose-50'],
                                'no_show'    => ['No-show',     'text-slate-500',   'bg-slate-50'],
                            ];
                        @endphp
                        @foreach($statusLabels as $status => [$label, $textColor, $bgColor])
                            <div class="p-5 text-center {{ $bgColor }}">
                                <p class="text-2xl font-black {{ $textColor }}">{{ $reservationStats[$status]->count ?? 0 }}</p>
                                <p class="text-xs font-semibold text-slate-500 mt-1">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
