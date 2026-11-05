<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class FiltroCajasRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->hasRole($this->user(), 'administrador')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:120'],
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
            'activa' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            'sede_id.exists' => 'La sede seleccionada no existe.',
            'activa.boolean' => 'Selecciona un estado válido.',
        ];
    }
}
