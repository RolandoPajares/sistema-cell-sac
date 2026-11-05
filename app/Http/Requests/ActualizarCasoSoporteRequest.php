<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ActualizarCasoSoporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.soporte.actualizar');
    }

    public function rules(): array
    {
        return [
            'asunto' => ['required', 'string', 'max:180'],
            'prioridad' => ['required', Rule::in(['baja', 'normal', 'alta', 'urgente'])],
            'estado' => ['required', Rule::in(['abierto', 'en_proceso', 'resuelto', 'cerrado'])],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
        ];
    }
}
