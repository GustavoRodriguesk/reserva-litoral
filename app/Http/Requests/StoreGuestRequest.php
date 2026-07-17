<?php

namespace App\Http\Requests;

use App\Models\Guest;
use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // tenant_id será injetado pelo model e RLS cuidará do isolamento.
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 
                'email', 
                'max:255',
                // A validação de único deve considerar o tenant (unique_with_tenant ou usar callback/Rule::unique)
                // Usaremos uma query mais segura na controller ou Model para lidar com RLS/tenant_id de forma elegante.
                // Mas para simplificar, a rule do laravel funciona se passarmos as constraints certas.
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Guest::query()
                        ->where('tenant_id', auth()->user()?->tenant_id)
                        ->where('email', $value)
                        ->exists()) {
                        $fail('Este e-mail já está cadastrado para este hóspede.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'document_type' => ['nullable', 'string', 'max:20'],
            'document_number' => [
                'nullable', 
                'string', 
                'max:40',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Guest::query()
                        ->where('tenant_id', auth()->user()?->tenant_id)
                        ->where('document_number', $value)
                        ->exists()) {
                        $fail('Este documento já está cadastrado para este hóspede.');
                    }
                },
            ],
            'birth_date' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'size:2'],
            'preferred_language' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
