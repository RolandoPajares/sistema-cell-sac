<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AjustarInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'compras_logistica', 'ventas_minoristas'], true);
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['sometimes', 'integer', 'exists:productos,id'],
            'tipo_movimiento' => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            'cantidad' => ['required', 'integer', 'min:1'],
            'notas' => ['required', 'string', 'max:500'],
        ];
    }
}
