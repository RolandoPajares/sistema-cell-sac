<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AjustarInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['sometimes', 'integer', 'exists:productos,id'],
            'almacen_id' => ['sometimes', 'required', 'integer', 'exists:almacenes,id'],
            'variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            'tipo_movimiento' => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            'cantidad' => ['required', 'integer', $this->input('tipo_movimiento') === 'ajuste' ? 'min:0' : 'min:1'],
            'notas' => ['required', 'string', 'max:500'],
        ];
    }
}
