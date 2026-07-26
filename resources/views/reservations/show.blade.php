<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Reserva #' . $reservation->locator_code" :subtitle="'Criada em ' . $reservation->created_at->format('d/m/Y H:i')" :backUrl="route('reservations.index')">
            <x-slot name="actions">
                <div class="flex items-center gap-2 mb-4 md:mb-0 mr-4">
                    @php
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'canceled' => 'bg-rose-100 text-rose-800 border-rose-200',
                            'no_show' => 'bg-slate-100 text-slate-800 border-slate-200',
                        ];
                        $stayStatusColors = [
                            'awaiting_checkin' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'checked_in' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            'checked_out' => 'bg-purple-100 text-purple-800 border-purple-200',
                        ];
                        $statusLabels = [
                            'pending' => 'Pendente',
                            'confirmed' => 'Confirmada',
                            'canceled' => 'Cancelada',
                            'no_show' => 'No-show',
                            'refunded' => 'Reembolsada',
                        ];
                        $stayStatusLabels = [
                            'awaiting_checkin' => 'Aguardando Check-in',
                            'checked_in' => 'Hospedado',
                            'checked_out' => 'Check-out Realizado',
                        ];
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusColors[$reservation->reservation_status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                        {{ $statusLabels[$reservation->reservation_status] ?? $reservation->reservation_status }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $stayStatusColors[$reservation->stay_status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                        {{ $stayStatusLabels[$reservation->stay_status] ?? $reservation->stay_status }}
                    </span>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    @if($reservation->reservation_status !== 'canceled' && $reservation->stay_status === 'awaiting_checkin')
                        <button type="button"
                                onclick="openModal('modal-checkin')"
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                            Realizar Check-in
                        </button>
                    @endif

                    @if($reservation->stay_status === 'checked_in')
                        <button type="button"
                                onclick="openModal('modal-checkout')"
                                class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25" /></svg>
                            Realizar Check-out
                        </button>
                    @endif

                    @if($reservation->reservation_status !== 'canceled' && $reservation->stay_status !== 'checked_out')
                        <form action="{{ route('reservations.cancel', $reservation) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Deseja realmente cancelar esta reserva?');"
                                    class="inline-flex items-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                Cancelar Reserva
                            </button>
                        </form>
                    @endif
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <x-flash-message />

            @if(session('checkout_balance_warning'))
                @php $balWarn = session('checkout_balance_warning'); @endphp
                <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-4 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4" role="alert">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        <div>
                            <p class="font-bold">Saldo devedor detectado</p>
                            <p class="text-sm mt-0.5">Esta reserva possui saldo em aberto de <strong>R$ {{ number_format($balWarn['balance'], 2, ',', '.') }}</strong>. Deseja forçar o check-out mesmo assim?</p>
                        </div>
                    </div>
                    <form action="{{ route('reservations.checkout', $balWarn['reservation_id']) }}" method="POST" class="shrink-0">
                        @csrf
                        <input type="hidden" name="force_checkout" value="1">
                        <button type="submit"
                                class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition whitespace-nowrap">
                            Forçar Check-out
                        </button>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Coluna Principal (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Informações Gerais da Hospedagem -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800">Detalhes da Hospedagem</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Hóspede Principal</span>
                                <span class="text-sm font-bold text-gray-900 mt-1 block">{{ $reservation->mainGuest->full_name }}</span>
                                <span class="text-xs text-gray-500 block">{{ $reservation->mainGuest->email ?? 'Sem e-mail' }}</span>
                                <span class="text-xs text-gray-500 block">{{ $reservation->mainGuest->phone ?? 'Sem telefone' }}</span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Acomodação</span>
                                @if($room)
                                    <span class="text-sm font-bold text-gray-900 mt-1 block">Quarto {{ $room->number }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $room->roomType->name }}</span>
                                @else
                                    <span class="text-sm font-bold text-gray-500 mt-1 block">Não atribuído</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Período</span>
                                <span class="text-sm font-bold text-gray-900 mt-1 block">
                                    {{ $reservation->check_in_date->format('d/m/Y') }} a {{ $reservation->check_out_date->format('d/m/Y') }}
                                </span>
                                <span class="text-xs text-gray-500 block">
                                    {{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }} noites · {{ $reservation->adults }} ad. / {{ $reservation->children }} cri.
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Seção de Cobranças -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800">Cobranças & Descontos</h3>
                            @if($reservation->reservation_status !== 'canceled')
                                <button onclick="openModal('modal-charge')" class="text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold px-3 py-1.5 rounded-lg border border-indigo-100 transition">
                                    Adicionar Cobrança
                                </button>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/30">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Item/Descrição</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Qtde</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Valor Unit.</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @php
                                        $subtotalCharges = 0;
                                        $subtotalDiscounts = 0;
                                    @endphp
                                    @forelse($reservation->charges as $charge)
                                        @php
                                            if ($charge->is_discount) {
                                                $subtotalDiscounts += (float) $charge->total_amount;
                                            } else {
                                                $subtotalCharges += (float) $charge->total_amount;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="flex items-center gap-2">
                                                    @if($charge->is_discount)
                                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                                    @else
                                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                                    @endif
                                                    {{ $charge->description }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                {{ number_format($charge->quantity, 0) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                R$ {{ number_format($charge->unit_amount, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-right {{ $charge->is_discount ? 'text-rose-600' : 'text-gray-900' }}">
                                                {{ $charge->is_discount ? '-' : '' }}R$ {{ number_format($charge->total_amount, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">Nenhuma cobrança registrada.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Seção de Pagamentos -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800">Histórico de Pagamentos</h3>
                            @if($reservation->reservation_status !== 'canceled')
                                <button onclick="openModal('modal-payment')" class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold px-3 py-1.5 rounded-lg border border-emerald-100 transition">
                                    Registrar Pagamento
                                </button>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50/30">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Data</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Valor Pago</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @php
                                        $totalPaid = 0;
                                    @endphp
                                    @forelse($reservation->payments as $payment)
                                        @php
                                            if ($payment->status === 'paid') {
                                                $totalPaid += (float) $payment->amount;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    {{ $payment->status === 'paid' ? 'Pago' : $payment->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                                R$ {{ number_format($payment->amount, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-400">Nenhum pagamento registrado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral (1/3) -->
                <div class="space-y-6">
                    
                    <!-- Resumo de Valores -->
                    <div class="bg-gray-900 text-white rounded-xl shadow-md overflow-hidden">
                        <div class="p-6 border-b border-gray-800 bg-gray-950/40">
                            <h3 class="font-bold">Resumo Financeiro</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between text-sm text-gray-400">
                                <span>Total de Cobranças</span>
                                <span class="text-gray-200">R$ {{ number_format($subtotalCharges, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-400">
                                <span>Total de Descontos</span>
                                <span class="text-gray-200">-R$ {{ number_format($subtotalDiscounts, 2, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-gray-800 my-2"></div>
                            <div class="flex justify-between text-base font-semibold">
                                <span>Valor Final da Reserva</span>
                                <span class="text-sky-400">R$ {{ number_format($reservation->total_amount, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-400">
                                <span>Total Pago</span>
                                <span class="text-emerald-400">R$ {{ number_format($totalPaid, 2, ',', '.') }}</span>
                            </div>
                            @php
                                $balance = max(0, (float) $reservation->total_amount - $totalPaid);
                            @endphp
                            <div class="flex justify-between text-sm font-semibold">
                                <span>Saldo Devedor</span>
                                <span class="{{ $balance > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                                    R$ {{ number_format($balance, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline de Eventos -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800">Histórico / Timeline</h3>
                        </div>
                        <div class="p-6">
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    @forelse($reservation->events as $event)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center ring-8 ring-white">
                                                            @if($event->event_type === 'reservation_created')
                                                                📅
                                                            @elseif($event->event_type === 'checkin_performed')
                                                                🔑
                                                            @elseif($event->event_type === 'checkout_performed')
                                                                🧹
                                                            @elseif($event->event_type === 'payment_received')
                                                                💵
                                                            @elseif($event->event_type === 'charge_added')
                                                                🏷️
                                                            @elseif($event->event_type === 'reservation_canceled')
                                                                ❌
                                                            @else
                                                                🔔
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="flex-1 min-w-0 pt-1.5">
                                                        <p class="text-sm font-semibold text-gray-900">{{ $event->description }}</p>
                                                        <div class="text-xs text-gray-500 mt-0.5">
                                                            <span>Por {{ $event->performer->name ?? 'Sistema' }}</span>
                                                            <span class="mx-1">•</span>
                                                            <span>{{ $event->performed_at->format('d/m H:i') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-center text-sm text-gray-400 py-4">Nenhum evento registrado.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cobrança -->
    <div id="modal-charge" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('modal-charge')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('reservations.charges.store', $reservation) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Adicionar Cobrança ou Desconto</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                <select name="charge_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="servico_extra">Serviço Extra</option>
                                    <option value="taxa_limpeza">Taxa de Limpeza</option>
                                    <option value="desconto">Desconto/Ajuste</option>
                                    <option value="diaria">Diária Adicional</option>
                                    <option value="imposto">Imposto</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição / Item</label>
                                <input type="text" name="description" placeholder="Ex: Café da manhã, Estacionamento, etc." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                                    <input type="number" step="1" name="quantity" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Valor Unitário (R$)</label>
                                    <input type="number" step="0.01" name="unit_amount" placeholder="0.00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Esta cobrança é um Desconto?</label>
                                <select name="is_discount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="0">Não (Acrescenta ao valor)</option>
                                    <option value="1">Sim (Reduz o valor)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button" onclick="closeModal('modal-charge')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pagamento -->
    <div id="modal-payment" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('modal-payment')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('reservations.payments.store', $reservation) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Registrar Pagamento</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                                <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="pix">PIX</option>
                                    <option value="credit_card">Cartão de Crédito</option>
                                    <option value="debit_card">Cartão de Débito</option>
                                    <option value="cash">Dinheiro</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="bank_transfer">Transferência Bancária</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Pago (R$)</label>
                                <input type="number" step="0.01" name="amount" value="{{ $balance }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Confirmar Pagamento
                        </button>
                        <button type="button" onclick="closeModal('modal-payment')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Modal: Check-in ===== --}}
    @if($reservation->stay_status === 'awaiting_checkin')
    <div id="modal-checkin" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-checkin-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-checkin')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all sm:my-8">

                <div class="bg-indigo-600 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                        </div>
                        <div>
                            <h3 id="modal-checkin-title" class="text-lg font-bold text-white">Confirmar Check-in</h3>
                            <p class="text-indigo-200 text-sm">Reserva #{{ $reservation->locator_code }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('reservations.checkin', $reservation) }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-5">

                        {{-- Informações rápidas --}}
                        <div class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4 text-sm">
                            <div>
                                <p class="text-slate-500">Hóspede principal</p>
                                <p class="font-semibold text-slate-800">{{ $reservation->mainGuest?->full_name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Quarto</p>
                                @foreach($reservation->rooms as $rr)
                                    <p class="font-semibold text-slate-800">{{ $rr->number }}</p>
                                @endforeach
                            </div>
                            <div>
                                <p class="text-slate-500">Check-in previsto</p>
                                <p class="font-semibold text-slate-800">
                                    {{ $reservation->check_in_date ? $reservation->check_in_date->format('d/m/Y') : '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-slate-500">Check-out previsto</p>
                                <p class="font-semibold text-slate-800">
                                    {{ $reservation->check_out_date ? $reservation->check_out_date->format('d/m/Y') : '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Documento verificado --}}
                        <div class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
                            <input id="document_verified" name="document_verified" type="checkbox" value="1" checked
                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <label for="document_verified" class="text-sm font-semibold text-slate-700 cursor-pointer">Documento verificado</label>
                                <p class="text-xs text-slate-500 mt-0.5">Confirmo que o documento de identidade do hóspede foi conferido presencialmente.</p>
                            </div>
                        </div>

                        {{-- Observações --}}
                        <div>
                            <label for="checkin_notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Observações (opcional)</label>
                            <textarea id="checkin_notes" name="checkin_notes" rows="2"
                                      placeholder="Ex: Hóspede solicitou cama extra, berço, etc."
                                      class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                    </div>
                    <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button type="submit"
                                class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Confirmar Check-in
                        </button>
                        <button type="button" onclick="closeModal('modal-checkin')"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif

    {{-- ===== Modal: Check-out ===== --}}
    @if($reservation->stay_status === 'checked_in')
    <div id="modal-checkout" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-checkout-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-checkout')"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all sm:my-8">

                <div class="bg-purple-600 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25" /></svg>
                        </div>
                        <div>
                            <h3 id="modal-checkout-title" class="text-lg font-bold text-white">Confirmar Check-out</h3>
                            <p class="text-purple-200 text-sm">Reserva #{{ $reservation->locator_code }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('reservations.checkout', $reservation) }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-5">

                        {{-- Saldo financeiro --}}
                        @php
                            $paidTotal  = $reservation->payments->where('status','paid')->sum('amount');
                            $balDue     = max(0, (float)$reservation->total_amount - (float)$paidTotal);
                        @endphp

                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3 text-center">
                                <p class="text-xs text-slate-500">Total</p>
                                <p class="font-bold text-slate-800 mt-0.5">R$ {{ number_format($reservation->total_amount, 2, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl bg-emerald-50 p-3 text-center">
                                <p class="text-xs text-emerald-600">Pago</p>
                                <p class="font-bold text-emerald-700 mt-0.5">R$ {{ number_format($paidTotal, 2, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl {{ $balDue > 0 ? 'bg-rose-50' : 'bg-slate-50' }} p-3 text-center">
                                <p class="text-xs {{ $balDue > 0 ? 'text-rose-600' : 'text-slate-500' }}">Saldo</p>
                                <p class="font-bold {{ $balDue > 0 ? 'text-rose-700' : 'text-slate-800' }} mt-0.5">R$ {{ number_format($balDue, 2, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($balDue > 0)
                            <div class="flex items-start gap-2.5 rounded-xl bg-amber-50 border border-amber-200 p-3.5 text-sm text-amber-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                <span>Há saldo devedor. O check-out ficará <strong>bloqueado</strong> se não for quitado. Clique em "Forçar" apenas em casos excepcionais.</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-sm text-emerald-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Reserva <strong>quitada</strong>. Check-out liberado.</span>
                            </div>
                        @endif

                        {{-- Relatório de danos --}}
                        <div>
                            <label for="damage_report" class="block text-sm font-semibold text-slate-700 mb-1.5">Relatório de danos (opcional)</label>
                            <textarea id="damage_report" name="damage_report" rows="2"
                                      placeholder="Ex: Toalha danificada, controle remoto desaparecido..."
                                      class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
                        </div>

                        {{-- Observações --}}
                        <div>
                            <label for="checkout_notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Observações (opcional)</label>
                            <textarea id="checkout_notes" name="checkout_notes" rows="2"
                                      placeholder="Ex: Hóspede deixou chaves, pediu nota fiscal..."
                                      class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
                        </div>

                    </div>
                    <div class="flex gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        @if($balDue > 0)
                            @php $balDueFormatted = number_format($balDue, 2, ',', '.'); @endphp
                            {{-- Com saldo: botão de forçar à esquerda, cancelar à direita --}}
                            <button type="submit" name="force_checkout" value="1"
                                    onclick="return confirm('Confirmar check-out com saldo devedor de R$ {{ $balDueFormatted }}?')"
                                    class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                Forçar Check-out
                            </button>
                        @else
                            <button type="submit"
                                    class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-purple-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Confirmar Check-out
                            </button>

                        @endif
                        <button type="button" onclick="closeModal('modal-checkout')"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endif

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        // Fecha modal ao pressionar Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['modal-checkin','modal-checkout','modal-payment','modal-charge'].forEach(closeModal);
            }
        });
    </script>
</x-app-layout>
