<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        Reserva #{{ $reservation->locator_code }}
                    </h2>
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
                <p class="text-sm text-gray-500 mt-1">Criada em {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                @if($reservation->reservation_status !== 'canceled' && $reservation->stay_status === 'awaiting_checkin')
                    <form action="{{ route('reservations.checkin', $reservation) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm">
                            Realizar Check-in
                        </button>
                    </form>
                @endif

                @if($reservation->stay_status === 'checked_in')
                    <form action="{{ route('reservations.checkout', $reservation) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm" onclick="return confirm('Confirmar check-out e liberação do quarto para limpeza?');">
                            Realizar Check-out
                        </button>
                    </form>
                @endif

                @if($reservation->reservation_status !== 'canceled' && $reservation->stay_status !== 'checked_out')
                    <form action="{{ route('reservations.cancel', $reservation) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm" onclick="return confirm('Deseja realmente cancelar esta reserva?');">
                            Cancelar Reserva
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
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

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
</x-app-layout>
