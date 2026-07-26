<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura {{ $invoice->invoice_number }} — {{ $hotel->name ?? 'Hotel' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-shadow { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4">

    <!-- Barra de Ações (só na tela, não imprime) -->
    <div class="no-print max-w-3xl mx-auto mb-6 flex items-center justify-between">
        <a href="{{ route('reservations.show', $invoice->reservation_id) }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900">
            ← Voltar à Reserva
        </a>
        <div class="flex gap-3">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition">
                🖨️ Imprimir / Salvar PDF
            </button>
        </div>
    </div>

    <!-- Documento da Fatura -->
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg print-shadow overflow-hidden">

        <!-- Cabeçalho -->
        <div class="bg-gray-900 text-white px-8 py-8 flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold">{{ $hotel->name ?? 'Hotel' }}</h1>
                @if($hotel->address ?? null)
                    <p class="text-gray-400 text-sm mt-1">{{ $hotel->address }}</p>
                @endif
                @if($hotel->cnpj ?? null)
                    <p class="text-gray-400 text-sm">CNPJ: {{ $hotel->cnpj }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Fatura</p>
                <p class="text-3xl font-bold text-sky-400 mt-1">{{ $invoice->invoice_number }}</p>
                <p class="text-gray-400 text-sm mt-2">
                    Emitida em {{ $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : now()->format('d/m/Y') }}
                </p>
                <span class="mt-2 inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold
                    {{ $invoice->status === 'issued' ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-gray-900' }}">
                    {{ $invoice->status === 'issued' ? 'Emitida' : ucfirst($invoice->status) }}
                </span>
            </div>
        </div>

        <!-- Dados do Hóspede e Reserva -->
        <div class="px-8 py-6 border-b border-gray-100 grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Hóspede</p>
                <p class="font-bold text-gray-900 text-base">{{ $invoice->reservation->mainGuest->full_name }}</p>
                @if($invoice->reservation->mainGuest->email)
                    <p class="text-sm text-gray-500">{{ $invoice->reservation->mainGuest->email }}</p>
                @endif
                @if($invoice->reservation->mainGuest->document_number)
                    <p class="text-sm text-gray-500">
                        {{ $invoice->reservation->mainGuest->document_type ? $invoice->reservation->mainGuest->document_type.': ' : '' }}
                        {{ $invoice->reservation->mainGuest->document_number }}
                    </p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Reserva</p>
                <p class="font-bold text-gray-900">#{{ $invoice->reservation->locator_code }}</p>
                @php
                    $roomRelation = $invoice->reservation->rooms->first();
                    $nights = $invoice->reservation->check_in_date->diffInDays($invoice->reservation->check_out_date);
                @endphp
                @if($roomRelation)
                    <p class="text-sm text-gray-500">Quarto {{ $roomRelation->number }} — {{ $roomRelation->roomType->name ?? '—' }}</p>
                @endif
                <p class="text-sm text-gray-500">
                    {{ $invoice->reservation->check_in_date->format('d/m/Y') }}
                    → {{ $invoice->reservation->check_out_date->format('d/m/Y') }}
                    ({{ $nights }} {{ $nights == 1 ? 'noite' : 'noites' }})
                </p>
            </div>
        </div>

        <!-- Itens -->
        <div class="px-8 py-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-4">Itens da Fatura</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 font-semibold text-gray-500 text-xs uppercase">Descrição</th>
                        <th class="text-center py-2 font-semibold text-gray-500 text-xs uppercase w-16">Qtde</th>
                        <th class="text-right py-2 font-semibold text-gray-500 text-xs uppercase w-28">Valor Unit.</th>
                        <th class="text-right py-2 font-semibold text-gray-500 text-xs uppercase w-28">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3 text-gray-800">
                                {{ $item->description }}
                                @if((float)$item->total_amount < 0)
                                    <span class="ml-1 text-xs text-rose-500 font-medium">(desconto)</span>
                                @endif
                            </td>
                            <td class="py-3 text-center text-gray-600">{{ number_format($item->quantity, 0) }}</td>
                            <td class="py-3 text-right text-gray-600">R$ {{ number_format(abs($item->unit_amount), 2, ',', '.') }}</td>
                            <td class="py-3 text-right font-semibold {{ (float)$item->total_amount < 0 ? 'text-rose-600' : 'text-gray-900' }}">
                                {{ (float)$item->total_amount < 0 ? '-' : '' }}R$ {{ number_format(abs($item->total_amount), 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totais -->
        <div class="px-8 pb-6">
            <div class="bg-gray-50 rounded-xl p-5 space-y-3">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span>R$ {{ number_format($invoice->items->where('total_amount', '>', 0)->sum('total_amount'), 2, ',', '.') }}</span>
                </div>
                @if($invoice->items->where('total_amount', '<', 0)->count() > 0)
                    <div class="flex justify-between text-sm text-rose-600">
                        <span>Descontos</span>
                        <span>-R$ {{ number_format(abs($invoice->items->where('total_amount', '<', 0)->sum('total_amount')), 2, ',', '.') }}</span>
                    </div>
                @endif
                @if((float)$invoice->tax_amount > 0)
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Taxas / Impostos</span>
                        <span>R$ {{ number_format($invoice->tax_amount, 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="border-t border-gray-200 pt-3 flex justify-between text-base font-bold text-gray-900">
                    <span>Total da Fatura</span>
                    <span>R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm text-emerald-600 font-medium">
                    <span>Total Pago</span>
                    <span>R$ {{ number_format($totalPaid, 2, ',', '.') }}</span>
                </div>
                @if($balance > 0)
                    <div class="flex justify-between text-sm font-bold text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
                        <span>Saldo a Pagar</span>
                        <span>R$ {{ number_format($balance, 2, ',', '.') }}</span>
                    </div>
                @else
                    <div class="flex justify-between text-sm font-bold text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2">
                        <span>✓ Totalmente Pago</span>
                        <span>R$ 0,00</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pagamentos realizados -->
        @if($invoice->reservation->payments->count() > 0)
        <div class="px-8 pb-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Pagamentos Recebidos</p>
            <div class="space-y-2">
                @foreach($invoice->reservation->payments->where('status', 'paid') as $payment)
                    <div class="flex justify-between items-center text-sm bg-gray-50 rounded-lg px-4 py-2.5">
                        <span class="text-gray-600">
                            {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}
                        </span>
                        <span class="font-semibold text-gray-900">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Rodapé -->
        <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">
                Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }} — {{ $hotel->name ?? 'Hotel' }}
                @if($hotel->phone ?? null)
                    · {{ $hotel->phone }}
                @endif
            </p>
            <p class="text-xs text-gray-300 mt-1">Obrigado pela sua hospedagem!</p>
        </div>
    </div>
</body>
</html>
