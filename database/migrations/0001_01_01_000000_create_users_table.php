<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nota: a tabela iam.users é criada e gerenciada via schema SQL direto
 * (schema/reserva-litoral_pms_schema_v3.sql). Esta migration NÃO recria
 * essa tabela para evitar conflitos com o schema PostgreSQL existente.
 *
 * O que esta migration cria:
 *   - public.password_reset_tokens  (broker de reset de senha do Breeze)
 *   - public.sessions               (driver de sessão do Laravel, user_id em UUID)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // user_id referencia iam.users.id (UUID) — usamos uuid nullable em vez
            // de foreignId (bigint) para compatibilidade com a PK UUID do schema iam.
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
    }
};
