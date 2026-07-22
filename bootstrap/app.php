<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetTenantContext::class,
            \App\Http\Middleware\EnsureTenantExists::class,
            \App\Http\Middleware\EnsureActiveUser::class,
            \App\Http\Middleware\UpdateLastActivity::class,
        ]);

        // Garante que SetTenantContext rode ANTES do SubstituteBindings.
        // Sem isso, o route model binding (Room $room) executa a query
        // sem o tenant configurado no PostgreSQL e a RLS retorna 0 linhas → 404.
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \App\Http\Middleware\SetTenantContext::class,
            \App\Http\Middleware\EnsureTenantExists::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        // Redireciona usuários não autenticados (convidados) que tentam acessar rotas protegidas
        $middleware->redirectTo(fn ($request) => route('login'));

        // Redireciona usuários já autenticados que tentam acessar rotas de convidados (como /login ou /register)
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Mapeia e traduz exceções brutas do PostgreSQL para feedbacks amigáveis
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            $sqlState = $e->errorInfo[0] ?? null;
            $message = $e->getMessage();
            $friendlyMessage = null;

            if ($sqlState === '23505') {
                $friendlyMessage = 'Este registro já está cadastrado no sistema (e-mail, documento ou identificador duplicado).';
            } elseif ($sqlState === '23503') {
                $friendlyMessage = 'Não é possível realizar esta operação pois este registro está vinculado a outras informações ativas no sistema.';
            } elseif ($sqlState === '23P01') {
                $friendlyMessage = 'Não foi possível confirmar a reserva: o quarto selecionado já está ocupado no período solicitado (overbooking bloqueado).';
            } elseif ($sqlState === '42501' && str_contains($message, 'row-level security policy')) {
                $friendlyMessage = 'Acesso negado: Você não tem permissão para visualizar ou alterar estas informações (restrição de tenant).';
            }

            if ($friendlyMessage) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'database_error',
                        'message' => $friendlyMessage,
                        'debug' => config('app.debug') ? $e->getMessage() : null,
                    ], $sqlState === '42501' ? 403 : 422);
                }

                return back()->withInput()->withErrors(['database_error' => $friendlyMessage]);
            }

            return null;
        });
    })->create();
