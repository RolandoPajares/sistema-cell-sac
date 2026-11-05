<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GuardarCasoSoporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.soporte.crear');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('casos_soporte', 'codigo')],
            'asunto' => ['required', 'string', 'max:180'],
            'prioridad' => ['required', Rule::in(['baja', 'normal', 'alta', 'urgente'])],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
        ];
    }
}
