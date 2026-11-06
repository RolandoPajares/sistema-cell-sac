<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgregarCarritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:20'],
            'variante_id' => ['nullable', 'integer', 'min:1'],
            'comprar_ahora' => ['nullable', 'boolean'],
        ];
    }
}
