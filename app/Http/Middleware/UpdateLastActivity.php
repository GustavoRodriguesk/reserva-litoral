<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Atualiza last_login_at diretamente no PostgreSQL com uma única query atômica.
            // A comparação de tempo é feita inteiramente no Postgres (now() vs last_login_at),
            // eliminando problemas de timezone entre PHP/Carbon e TIMESTAMPTZ do Postgres.
            // O WHERE garante o throttle de 5 minutos: se a última atividade foi recente,
            // 0 linhas são afetadas e nenhuma escrita desnecessária ocorre.
            DB::update(
                'UPDATE iam.users SET last_login_at = now(), updated_at = now()
                 WHERE id = ?
                   AND (last_login_at IS NULL OR last_login_at < now() - interval \'5 minutes\')',
                [Auth::id()]
            );
        }

        return $next($request);
    }
}
