<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Guest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Criar rota de teste com binding implícito
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/test-guest/{guest}', function (Guest $guest) {
        return "SUCESSO: Hóspede encontrado -> " . $guest->full_name;
    });
});

$user = User::first();
echo "Logando como User ID: {$user->id} (Tenant: {$user->tenant_id})\n";
auth()->login($user);

// Pegar um guest DO MESMO TENANT
$guest = Guest::where('tenant_id', $user->tenant_id)->first();
if (!$guest) {
    echo "Nenhum guest no tenant.\n";
    exit;
}

echo "Buscando Guest ID: {$guest->id}\n";
$request = Request::create("/test-guest/{$guest->id}", 'GET');
$response = app()->handle($request);

echo "Status (Mesmo Tenant): " . $response->getStatusCode() . "\n";
echo "Conteúdo: " . $response->getContent() . "\n\n";

// Pegar um guest de OUTRO TENANT se houver
$otherGuest = Guest::where('tenant_id', '!=', $user->tenant_id)->first();
if ($otherGuest) {
    echo "Tentando buscar Guest ID: {$otherGuest->id} (de outro tenant)\n";
    $request2 = Request::create("/test-guest/{$otherGuest->id}", 'GET');
    $response2 = app()->handle($request2);
    
    echo "Status (Outro Tenant): " . $response2->getStatusCode() . "\n";
    echo "Conteúdo: " . $response2->getContent() . "\n";
} else {
    echo "Nenhum guest de outro tenant para testar.\n";
}
