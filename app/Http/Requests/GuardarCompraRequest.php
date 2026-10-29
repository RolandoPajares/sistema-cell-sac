<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'compras_logistica'], true);
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_compra' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.costo_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }
}
