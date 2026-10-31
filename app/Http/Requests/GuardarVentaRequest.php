<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:users,id'],
            'tipo_venta' => ['required', 'in:tienda_online,fisica'],
            'fecha_venta' => ['required', 'date'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
