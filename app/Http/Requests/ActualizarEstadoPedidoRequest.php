<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarEstadoPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'ventas_minoristas', 'ventas_mayoristas'], true);
    }

    public function rules(): array
    {
        return ['estado' => ['required', Rule::in(['Pendiente', 'En proceso', 'Enviado', 'Entregado', 'Cancelado'])]];
    }
}
