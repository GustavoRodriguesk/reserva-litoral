<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Gera (ou recupera) a fatura de uma reserva.
     * Se já existir uma fatura para esta reserva, redireciona para ela.
     * Caso contrário, cria uma nova com base nas cobranças existentes.
     */
    public function store(Request $request, string $reservation)
    {
        $reservation = Reservation::query()
            ->whereKey($reservation)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        // Verifica se já existe fatura para esta reserva
        $existing = Invoice::query()
            ->where('reservation_id', $reservation->id)
            ->where('status', '!=', 'canceled')
            ->first();

        if ($existing) {
            return redirect()
                ->route('invoices.show', $existing)
                ->with('info', 'Esta reserva já possui uma fatura emitida.');
        }

        $reservation->load(['charges', 'mainGuest', 'rooms.roomType']);

        if ($reservation->charges->isEmpty()) {
            return back()->with('error', 'Esta reserva não possui cobranças para faturar.');
        }

        $invoice = DB::transaction(function () use ($reservation) {
            // Gera número sequencial da fatura por hotel
            $lastNumber = Invoice::query()
                ->where('hotel_id', $reservation->hotel_id)
                ->max(DB::raw("CAST(SPLIT_PART(invoice_number, '-', 2) AS INTEGER)"));

            $nextNumber = ($lastNumber ?? 0) + 1;
            $invoiceNumber = 'FAT-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Calcula totais
            $totalAmount = 0;
            foreach ($reservation->charges as $charge) {
                if ($charge->is_discount) {
                    $totalAmount -= (float) $charge->total_amount;
                } else {
                    $totalAmount += (float) $charge->total_amount;
                }
            }
            $totalAmount = max(0, $totalAmount);

            // Cria a fatura
            $invoice = Invoice::create([
                'hotel_id'       => $reservation->hotel_id,
                'reservation_id' => $reservation->id,
                'invoice_number' => $invoiceNumber,
                'status'         => 'issued',
                'total_amount'   => $totalAmount,
                'tax_amount'     => 0,
                'issued_at'      => now(),
            ]);

            // Cria os itens a partir das cobranças
            foreach ($reservation->charges as $charge) {
                $invoice->items()->create([
                    'description'  => $charge->description,
                    'quantity'     => $charge->quantity,
                    'unit_amount'  => $charge->unit_amount,
                    'total_amount' => $charge->is_discount
                        ? -1 * (float) $charge->total_amount
                        : (float) $charge->total_amount,
                ]);
            }

            // Registra evento na timeline
            $reservation->events()->create([
                'event_type'   => 'invoice_issued',
                'description'  => "Fatura emitida: {$invoiceNumber} (R$ " . number_format($totalAmount, 2, ',', '.') . ")",
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', "Fatura {$invoice->invoice_number} emitida com sucesso!");
    }

    /**
     * Exibe a fatura (view imprimível).
     */
    public function show(string $invoice)
    {
        $invoice = Invoice::query()
            ->whereKey($invoice)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->with(['items', 'reservation.mainGuest', 'reservation.rooms.roomType', 'reservation.payments'])
            ->firstOrFail();

        // Dados do hotel
        $hotel = DB::selectOne(
            'SELECT h.*, t.name AS tenant_name
               FROM core.hotels h
               JOIN iam.tenants t ON t.id = h.tenant_id
              WHERE h.id = ?',
            [$invoice->hotel_id]
        );

        $totalPaid = $invoice->reservation->payments
            ->where('status', 'paid')
            ->sum('amount');

        $balance = max(0, (float) $invoice->total_amount - (float) $totalPaid);

        return view('invoices.show', compact('invoice', 'hotel', 'totalPaid', 'balance'));
    }
}
