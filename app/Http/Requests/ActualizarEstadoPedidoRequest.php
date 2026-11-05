<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarEstadoPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.pedidos.actualizar');
    }

    public function rules(): array
    {
        return ['estado' => ['required', Rule::in(['Pendiente', 'En proceso', 'Enviado', 'Entregado', 'Cancelado'])]];
    }
}
