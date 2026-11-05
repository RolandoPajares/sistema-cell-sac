<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

final class ActualizarIntegracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.integraciones.actualizar');
    }

    public function rules(): array
    {
        return [
            'activa' => ['required', 'boolean'],
        ];
    }
}
