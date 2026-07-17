<?php

namespace App\Http\Requests;

use App\Models\Guest;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'room_id' => ['required', 'uuid', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! Room::query()->whereKey($value)->exists()) {
                    $fail('O quarto selecionado não existe.');
                }
            }],
            'guest_id' => ['required', 'uuid', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! Guest::query()->whereKey($value)->exists()) {
                    $fail('O hóspede selecionado não existe.');
                }
            }],
            'extras' => ['nullable', 'array'],
            'extras.*' => ['string', 'in:cafe,estacionamento,berco,pet,cama_extra'],
            'payment_method' => ['required', 'string', 'in:pix,credit_card,cash,pending'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
