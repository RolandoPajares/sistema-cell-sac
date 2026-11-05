<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ActualizarEstadoEnvioPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.inventario.ajustar');
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['pendiente', 'preparando', 'despachado', 'en_transito', 'entregado', 'incidencia', 'cancelado'])],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'identificador_externo' => ['nullable', 'string', 'max:160', 'regex:/\A[A-Za-z0-9._:-]+\z/'],
            'ocurrido_en' => ['nullable', 'date'],
        ];
    }
}
