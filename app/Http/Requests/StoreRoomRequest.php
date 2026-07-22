<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => [
                'required',
                'uuid',
                'exists:pgsql.core.hotels,id',
            ],

            'room_type_id' => [
                'required',
                'uuid',
                'exists:pgsql.booking.room_types,id',
            ],

            'number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('pgsql.booking.rooms', 'number')
                    ->where(fn ($query) => $query->where('hotel_id', $this->hotel_id)),
            ],

            'floor' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'hotel_id.required' => 'Selecione um hotel.',
            'room_type_id.required' => 'Selecione um tipo de quarto.',
            'number.required' => 'Informe o número do quarto.',
            'number.unique' => 'Já existe um quarto com este número neste hotel.',
        ];
    }
}