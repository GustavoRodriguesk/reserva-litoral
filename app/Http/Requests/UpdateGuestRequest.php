<?php

namespace App\Http\Requests;

use App\Models\Guest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGuestRequest extends FormRequest
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
        // O ID do guest vem da rota: $this->route('guest') -> pode ser a model ou o ID de fato.
        $guestId = $this->route('guest') instanceof Guest
            ? $this->route('guest')->id 
            : $this->route('guest');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 
                'email', 
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($guestId): void {
                    if (Guest::query()
                        ->where('tenant_id', auth()->user()?->tenant_id)
                        ->where('email', $value)
                        ->where('id', '!=', $guestId)
                        ->exists()) {
                        $fail('Este e-mail já está cadastrado para outro hóspede.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'document_type' => ['nullable', 'string', 'max:20'],
            'document_number' => [
                'nullable', 
                'string', 
                'max:40',
                function (string $attribute, mixed $value, \Closure $fail) use ($guestId): void {
                    if (Guest::query()
                        ->where('tenant_id', auth()->user()?->tenant_id)
                        ->where('document_number', $value)
                        ->where('id', '!=', $guestId)
                        ->exists()) {
                        $fail('Este documento já está cadastrado para outro hóspede.');
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
