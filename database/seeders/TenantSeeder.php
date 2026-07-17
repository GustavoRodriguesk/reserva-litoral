<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 'e7b99c71-c068-45a2-83fe-4b149b0713b1';
        $hotelId = 'd86d63d8-21d1-4be6-a4c6-2c9e7b23cf44';

        // 1. Criar o Tenant Padrão de desenvolvimento
        \Illuminate\Support\Facades\DB::table('iam.tenants')->updateOrInsert(
            ['id' => $tenantId],
            [
                'name' => 'Reserva Litoral',
                'slug' => 'reserva-litoral',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Criar o Hotel Padrão associado ao Tenant
        \Illuminate\Support\Facades\DB::table('core.hotels')->updateOrInsert(
            ['id' => $hotelId],
            [
                'tenant_id' => $tenantId,
                'name' => 'Reserva Litoral Hotel',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 3. Criar o Usuário Administrador de desenvolvimento
        $adminEmail = 'admin@reservalitoral.com.br';
        $userExists = \Illuminate\Support\Facades\DB::table('iam.users')
            ->where('email', $adminEmail)
            ->exists();

        if (!$userExists) {
            \App\Models\User::create([
                'tenant_id' => $tenantId,
                'name' => 'Admin Reserva Litoral',
                'email' => $adminEmail,
                'password_hash' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'is_active' => true,
                'locale' => 'pt-BR',
            ]);
        }
    }
}
