<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Http\Request;


class ReservationController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected ReservationService $reservationService,
    ) {}

    public function index(Request $request)
    {
        $query = Reservation::query()
            ->where('hotel_id', auth()->user()->hotel_id)
            ->with('mainGuest');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('locator_code', 'ilike', "%{$search}%")
                  ->orWhereHas('mainGuest', function ($g) use ($search) {
                      $g->where('full_name', 'ilike', "%{$search}%");
                  });
            });
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('reservations.index', compact('reservations', 'search'));
    }

    public function create()
    {
        return view('reservations.create');
    }

    
    public function availability(Request $request)
    {
        $validated = $request->validate([
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ]);

        $rooms = $this->availabilityService->availableRooms(
            hotelId: auth()->user()->hotel_id,
            checkIn: $validated['check_in'],
            checkOut: $validated['check_out'],
            adults: $validated['adults'],
            children: $validated['children'] ?? 0,
        );

        return view('reservations.create', [
            'rooms' => $rooms,
            'filters' => $validated,
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'room' => ['required', 'uuid'],
        ]);

        $room = Room::query()
            ->with('roomType')
            ->where('id', $data['room'])
            ->where('hotel_id', auth()->user()->hotel_id)
            ->where('status', 'available')
            ->firstOrFail();

        $guests = Guest::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'document_number']);

        $nights = \Carbon\Carbon::parse($data['check_in'])->diffInDays($data['check_out']);
        $totalAmount = $nights * (float) $room->roomType->base_price;

        return view('reservations.confirm', compact('data', 'room', 'guests', 'nights', 'totalAmount'));
    }

    public function store(StoreReservationRequest $request)
    {
        $reservation = $this->reservationService->create(
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Reserva criada com sucesso.');
    }

    public function show(string $reservation)
    {
        // Não use binding implícito aqui: ele ocorre antes do middleware auth
        // e pode ser bloqueado pelo RLS por ainda não haver tenant no contexto.
        $reservation = Reservation::query()
            ->whereKey($reservation)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        $reservation->load([
            'mainGuest',
            'rooms.room.roomType',
            'charges',
            'payments',
            'events.performer'
        ]);

        $room = $reservation->rooms->first()?->room;

        // Se a reserva acabou de ser criada e não tem cobrança de diária,
        // adiciona a cobrança de diária padrão correspondente no banco.
        if ($reservation->charges->count() === 0) {
            $nights = \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays($reservation->check_out_date);
            $roomRelation = $reservation->rooms->first();
            if ($roomRelation) {
                $reservation->charges()->create([
                    'charge_type' => 'diaria',
                    'description' => "Diárias ({$nights} noites no Quarto {$roomRelation->room->number})",
                    'quantity' => $nights,
                    'unit_amount' => $roomRelation->rate_per_night,
                    'total_amount' => $reservation->total_amount,
                    'is_discount' => false,
                ]);
                $reservation->load('charges');
            }
        }

        return view('reservations.show', compact('reservation', 'room'));
    }

    public function checkin(string $reservation)
    {
        $reservation = Reservation::query()
            ->whereKey($reservation)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        if ($reservation->stay_status !== 'awaiting_checkin') {
            return back()->with('error', 'Check-in não permitido para o status atual.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation) {
            \Illuminate\Support\Facades\DB::table('booking.checkins')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'reservation_id' => $reservation->id,
                'checked_in_by' => auth()->id(),
                'checked_in_at' => now(),
                'document_verified' => true,
            ]);

            $reservation->update([
                'stay_status' => 'checked_in',
            ]);

            foreach ($reservation->rooms as $resRoom) {
                $resRoom->room()->update(['status' => 'occupied']);
            }

            $reservation->events()->create([
                'event_type' => 'checkin_performed',
                'description' => 'Check-in realizado.',
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);
        });

        return back()->with('success', 'Check-in realizado com sucesso!');
    }

    public function checkout(string $reservation)
    {
        $reservation = Reservation::query()
            ->whereKey($reservation)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        if ($reservation->stay_status !== 'checked_in') {
            return back()->with('error', 'Check-out não permitido para o status atual.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation) {
            \Illuminate\Support\Facades\DB::table('booking.checkouts')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'reservation_id' => $reservation->id,
                'checked_out_by' => auth()->id(),
                'checked_out_at' => now(),
                'extra_amount' => 0,
            ]);

            $reservation->update([
                'stay_status' => 'checked_out',
            ]);

            foreach ($reservation->rooms as $resRoom) {
                $resRoom->room()->update(['status' => 'cleaning']);
            }

            $reservation->events()->create([
                'event_type' => 'checkout_performed',
                'description' => 'Check-out realizado.',
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);
        });

        return back()->with('success', 'Check-out realizado com sucesso!');
    }

    public function cancel(string $reservation)
    {
        $reservation = Reservation::query()
            ->whereKey($reservation)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        if (in_array($reservation->reservation_status, ['canceled', 'refunded'])) {
            return back()->with('error', 'Reserva já cancelada ou reembolsada.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation) {
            $reservation->update([
                'reservation_status' => 'canceled',
            ]);

            foreach ($reservation->rooms as $resRoom) {
                $resRoom->room()->update(['status' => 'available']);
            }

            $reservation->events()->create([
                'event_type' => 'reservation_canceled',
                'description' => 'Reserva cancelada.',
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);
        });

        return back()->with('success', 'Reserva cancelada com sucesso!');
    }

    public function addCharge(Request $request, string $reservation)
    {
        $reservation = Reservation::query()
            ->whereKey($reservation)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        $validated = $request->validate([
            'charge_type' => ['required', 'string', 'in:diaria,taxa_limpeza,desconto,cupom,imposto,servico_extra'],
            'description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_amount' => ['required', 'numeric', 'min:0'],
            'is_discount' => ['required', 'boolean'],
        ]);

        $totalAmount = round($validated['quantity'] * $validated['unit_amount'], 2);

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation, $validated, $totalAmount) {
            $reservation->charges()->create([
                'charge_type' => $validated['charge_type'],
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'unit_amount' => $validated['unit_amount'],
                'total_amount' => $totalAmount,
                'is_discount' => $validated['is_discount'],
            ]);

            $this->recalculateTotal($reservation);

            $chargeName = $validated['is_discount'] ? 'Desconto/Ajuste' : 'Cobrança';
            $reservation->events()->create([
                'event_type' => 'charge_added',
                'description' => "{$chargeName} adicionada: {$validated['description']} (R$ " . number_format($totalAmount, 2, ',', '.') . ")",
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);
        });

        return back()->with('success', 'Cobrança/desconto adicionado com sucesso!');
    }

    public function addPayment(Request $request, string $reservation)
    {
        $reservation = Reservation::query()
            ->whereKey($reservation)
            ->where('hotel_id', auth()->user()->hotel_id)
            ->firstOrFail();

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:credit_card,debit_card,pix,boleto,cash,bank_transfer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation, $validated) {
            $reservation->payments()->create([
                'amount' => $validated['amount'],
                'currency' => 'BRL',
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $methodLabels = [
                'credit_card' => 'Cartão de Crédito',
                'debit_card' => 'Cartão de Débito',
                'pix' => 'PIX',
                'boleto' => 'Boleto',
                'cash' => 'Dinheiro',
                'bank_transfer' => 'Transferência Bancária',
            ];
            $methodLabel = $methodLabels[$validated['payment_method']] ?? $validated['payment_method'];

            $reservation->events()->create([
                'event_type' => 'payment_received',
                'description' => "Pagamento recebido via {$methodLabel} (R$ " . number_format($validated['amount'], 2, ',', '.') . ")",
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);
        });

        return back()->with('success', 'Pagamento registrado com sucesso!');
    }

    private function recalculateTotal(Reservation $reservation)
    {
        $charges = $reservation->charges()->get();
        $subtotal = 0;
        $discounts = 0;

        foreach ($charges as $charge) {
            if ($charge->is_discount) {
                $discounts += (float) $charge->total_amount;
            } else {
                $subtotal += (float) $charge->total_amount;
            }
        }

        $total = max(0, $subtotal - $discounts);

        $reservation->update([
            'total_amount' => $total,
        ]);
    }

    public function apiAvailability(Request $request)
    {
        $validated = $request->validate([
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ]);

        $rooms = $this->availabilityService->availableRooms(
            hotelId: auth()->user()->hotel_id,
            checkIn: $validated['check_in'],
            checkOut: $validated['check_out'],
            adults: $validated['adults'],
            children: $validated['children'] ?? 0,
        );

        $nights = \Carbon\Carbon::parse($validated['check_in'])->diffInDays($validated['check_out']);

        return response()->json([
            'rooms' => $rooms->map(function ($room) use ($nights) {
                return [
                    'id' => $room->id,
                    'number' => $room->number,
                    'room_type_name' => $room->roomType->name,
                    'max_capacity' => $room->roomType->max_capacity,
                    'base_price' => (float) $room->roomType->base_price,
                    'estimate' => $nights * (float) $room->roomType->base_price,
                ];
            }),
            'nights' => $nights,
        ]);
    }

    public function apiGuests(Request $request)
    {
        $search = $request->input('search');
        if (empty($search)) {
            return response()->json([]);
        }

        $guests = Guest::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($query) use ($search) {
                $query->where('full_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('document_number', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            })
            ->orderBy('full_name')
            ->limit(10)
            ->get(['id', 'full_name', 'document_number', 'email', 'phone', 'document_type']);

        return response()->json($guests);
    }

    public function apiStoreGuest(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 
                'email', 
                'max:255',
                \Illuminate\Validation\Rule::unique('crm.guests', 'email')
                    ->where('tenant_id', auth()->user()?->tenant_id)
                    ->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'document_type' => ['nullable', 'string', 'max:20'],
            'document_number' => [
                'nullable', 
                'string', 
                'max:40',
                \Illuminate\Validation\Rule::unique('crm.guests', 'document_number')
                    ->where('tenant_id', auth()->user()?->tenant_id)
                    ->whereNull('deleted_at'),
            ],
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $guest = Guest::create($validated);

        return response()->json($guest);
    }
}
