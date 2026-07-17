<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class IamUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * Usa DB::selectOne com a função SECURITY DEFINER iam.find_user_by_id para
     * carregar o usuário da sessão mesmo antes de o SetTenantContext middleware
     * ter injetado o app.current_tenant na conexão. Sem isso, o retrieveById
     * padrão do Eloquent falha devido às policies RLS da tabela iam.users —
     * gerando loop infinito de redirecionamento entre /login e /dashboard.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        // DB::selectOne com cast ::UUID explícito evita o bug de binding
        // que ocorre ao usar fromRaw() com UUIDs (gera literal sem aspas).
        $row = DB::selectOne(
            'SELECT * FROM iam.find_user_by_id(?::UUID)',
            [$identifier]
        );

        if (! $row) {
            return null;
        }

        // Hidrata o Model Eloquent a partir do stdClass retornado pelo selectOne.
        $model = $this->createModel();

        return $model->newInstance([], true)->forceFill((array) $row)->syncOriginal();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            array_key_exists('password', $credentials))) {
            return null;
        }

        // Se contiver o email nas credenciais, buscamos usando a função customizada do PostgreSQL.
        // A função SECURITY DEFINER iam.find_user_by_email ignora RLS globalmente.
        if (isset($credentials['email'])) {
            $email = $credentials['email'];

            $model = $this->createModel();

            return $model->newQuery()
                ->fromRaw('iam.find_user_by_email(?)', [$email])
                ->first();
        }

        // Fallback para o comportamento padrão do EloquentUserProvider
        return parent::retrieveByCredentials($credentials);
    }
}
