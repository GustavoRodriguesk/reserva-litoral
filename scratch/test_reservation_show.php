<?php
require 'c:/laragon/www/reserva-litoral/vendor/autoload.php';
$app = require_once 'c:/laragon/www/reserva-litoral/bootstrap/app.php';
$consoleKernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();

use App\Models\User;
use App\Models\Reservation;

try {
    $user = User::first();
    auth()->login($user);

    if (class_exists(\App\Services\TenantContext::class)) {
        \App\Services\TenantContext::setTenantId($user->tenant_id);
    }

    $reservation = Reservation::find('019f6695-ffed-7189-bff7-b228d4f42b77') ?? Reservation::first();
    if (!$reservation) {
        echo "Nenhuma reserva encontrada.\n";
        exit;
    }

    echo "Reserva selecionada: #{$reservation->locator_code} (ID: {$reservation->id})\n";

    $reservation->load([
        'mainGuest',
        'rooms.roomType',
        'charges',
        'payments',
        'events.performer'
    ]);

    $room = $reservation->rooms->first();

    echo "Quarto associado: " . ($room ? "#" . $room->number : "Nenhum") . "\n";

    $html = view('reservations.show', compact('reservation', 'room'))->render();
    echo "✓ View reservations.show renderizada com sucesso! (" . strlen($html) . " bytes)\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
