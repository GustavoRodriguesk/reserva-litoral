<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Determina o tenant_id padrão de desenvolvimento (Reserva Litoral)
        $tenantId = 'e7b99c71-c068-45a2-83fe-4b149b0713b1';
        $userId = '';

        // Se o usuário estiver logado, usamos as informações do usuário autenticado
        if (Auth::check()) {
            $user = Auth::user();
            if ($user) {
                $tenantId = $user->tenant_id;
                $userId = $user->id;
            }
        }

        // 2. Injeta os parâmetros na sessão do banco de dados (PostgreSQL) usando set_config.
        // O parâmetro 'false' garante que a configuração persista por toda a conexão (sessão PDO).
        DB::statement("SELECT set_config('app.current_tenant', ?, false)", [$tenantId]);
        DB::statement("SELECT set_config('app.current_user', ?, false)", [$userId]);

        return $next($request);
    }
}
