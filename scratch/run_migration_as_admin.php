<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Config::set('database.connections.pgsql.username', 'postgres');
Config::set('database.connections.pgsql.password', 'root');

DB::purge('pgsql');
DB::reconnect('pgsql');

echo "Conectado como: " . DB::getConfig('username') . "\n";

try {
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Erro com postgres/root: " . $e->getMessage() . "\n\n";
    
    // Tentar com senha vazia
    Config::set('database.connections.pgsql.password', '');
    DB::purge('pgsql');
    DB::reconnect('pgsql');
    
    try {
        Artisan::call('migrate', ['--force' => true]);
        echo Artisan::output();
    } catch (\Exception $e) {
        echo "Erro com postgres/(vazio): " . $e->getMessage() . "\n";
    }
}
