<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para garantir que o owner das funções seja o usuário que executa a migration
        // (tipicamente postgres/superuser), usamos DROP + CREATE em vez de CREATE OR REPLACE.
        // Isso é crítico: SECURITY DEFINER só bypassa RLS se o owner for superuser.
        // Se o owner for reserva_app (role sem BYPASSRLS), o SECURITY DEFINER não ajuda.

        // Função para login: busca por email ignorando RLS
        DB::unprepared('DROP FUNCTION IF EXISTS iam.find_user_by_email(VARCHAR)');
        DB::unprepared("
            CREATE FUNCTION iam.find_user_by_email(p_email VARCHAR)
            RETURNS SETOF iam.users
            LANGUAGE plpgsql
            SECURITY DEFINER
            AS \$\$
            BEGIN
                RETURN QUERY 
                SELECT * FROM iam.users 
                WHERE email = p_email 
                  AND deleted_at IS NULL;
            END;
            \$\$;
        ");
        DB::unprepared('GRANT EXECUTE ON FUNCTION iam.find_user_by_email(VARCHAR) TO reserva_app');

        // Função para recarregar sessão: busca por UUID ignorando RLS.
        // Crítica para evitar loop infinito de redirect após login,
        // pois Auth::user() chama retrieveById ANTES do SetTenantContext middleware
        // ter injetado app.current_tenant na sessão do Postgres.
        DB::unprepared('DROP FUNCTION IF EXISTS iam.find_user_by_id(UUID)');
        DB::unprepared("
            CREATE FUNCTION iam.find_user_by_id(p_id UUID)
            RETURNS SETOF iam.users
            LANGUAGE plpgsql
            SECURITY DEFINER
            AS \$\$
            BEGIN
                RETURN QUERY 
                SELECT * FROM iam.users 
                WHERE id = p_id 
                  AND deleted_at IS NULL;
            END;
            \$\$;
        ");
        DB::unprepared('GRANT EXECUTE ON FUNCTION iam.find_user_by_id(UUID) TO reserva_app');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS iam.find_user_by_email(VARCHAR);");
        DB::unprepared("DROP FUNCTION IF EXISTS iam.find_user_by_id(UUID);");
    }
};
