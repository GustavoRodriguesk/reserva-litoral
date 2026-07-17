<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Determina o tenant_id com base no usuário logado ou no padrão de dev
        $tenantId = 'e7b99c71-c068-45a2-83fe-4b149b0713b1';
        if (Auth::check()) {
            $user = Auth::user();
            if ($user) {
                $tenantId = $user->tenant_id;
            }
        }

        // 2. Busca as informações do tenant na tabela iam.tenants (essa tabela não tem RLS aplicada)
        $tenant = DB::table('iam.tenants')
            ->where('id', $tenantId)
            ->first();

        // 3. Se o tenant não existir, estiver deletado ou inativo (suspended/canceled)
        if (!$tenant || !empty($tenant->deleted_at) || in_array($tenant->status, ['suspended', 'canceled'])) {
            
            // Se o usuário estiver autenticado, encerra a sessão
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            $errorMessage = 'Acesso Bloqueado: Este estabelecimento (tenant) está temporariamente suspenso ou inativo. Por favor, entre em contato com o suporte financeiro.';

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'tenant_inactive',
                    'message' => $errorMessage,
                ], 403);
            }

            abort(403, $errorMessage);
        }

        return $next($request);
    }
}
