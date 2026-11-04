<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarMovimientoCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->hasRole($this->user(), 'administrador')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return [
            'direccion' => ['required', Rule::in(['entrada', 'salida'])],
            'importe' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
            'descripcion' => ['required', 'string', 'max:500'],
            'token' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'direccion.required' => 'Selecciona entrada o salida.',
            'direccion.in' => 'La dirección del movimiento no es válida.',
            'importe.required' => 'Ingresa el importe.',
            'importe.regex' => 'El importe debe tener hasta dos decimales y no ser negativo.',
            'descripcion.required' => 'Describe el motivo del movimiento.',
            'descripcion.max' => 'La descripción no puede superar 500 caracteres.',
            'token.uuid' => 'El identificador de operación no es válido.',
        ];
    }
}
