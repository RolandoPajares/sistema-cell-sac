<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'ventas_mayoristas', 'cliente_mayorista'], true);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'notas' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ];
    }
}
