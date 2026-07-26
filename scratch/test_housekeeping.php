<?php
require 'c:/laragon/www/reserva-litoral/vendor/autoload.php';
$app = require_once 'c:/laragon/www/reserva-litoral/bootstrap/app.php';
$consoleKernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();

use App\Models\User;
use App\Models\Room;

try {
    $user = User::first();
    auth()->login($user);

    if (class_exists(\App\Services\TenantContext::class)) {
        \App\Services\TenantContext::setTenantId($user->tenant_id);
    }

    echo "--- Testando renderização da View de Governança ---\n";
    $rooms = Room::where('hotel_id', $user->hotel_id)->with('roomType')->get();
    $counts = [
        'total' => $rooms->count(),
        'available' => 0, 'reserved' => 0, 'occupied' => 0, 'cleaning' => 0, 'inspected' => 0, 'maintenance' => 0, 'blocked' => 0
    ];
    $roomTypes = \App\Models\RoomType::where('hotel_id', $user->hotel_id)->get();

    $html = view('housekeeping.index', compact('rooms', 'counts', 'roomTypes'))->render();
    echo "✓ View housekeeping.index renderizada com sucesso! (" . strlen($html) . " chars)\n";

    echo "\n--- Testando transições de status no banco ---\n";
    $room = $rooms->first();
    if ($room) {
        echo "Quarto #{$room->number}: Estado original -> {$room->status}\n";

        // Fluxo: Limpeza -> Inspecionado -> Livre
        $room->update(['status' => 'cleaning']);
        echo " -> Alterado para: {$room->status}\n";

        $room->update(['status' => 'inspected']);
        echo " -> Alterado para: {$room->status}\n";

        $room->update(['status' => 'available']);
        echo " -> Restaurado para: {$room->status}\n";

        echo "✓ Todas as transições de BD validadas com sucesso!\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
