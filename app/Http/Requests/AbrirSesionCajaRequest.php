<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase AbrirSesionCajaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AbrirSesionCajaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->allowsGlobal($this->user(), 'gestion.seguridad.consultar')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'caja_id' => ['required', 'integer', 'exists:cajas,id'],
            'saldo_inicial' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
            'token' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'caja_id.required' => 'Selecciona una caja.',
            'caja_id.exists' => 'La caja seleccionada no existe.',
            'saldo_inicial.required' => 'Ingresa el saldo inicial contado.',
            'saldo_inicial.regex' => 'El saldo inicial debe ser positivo o cero y tener hasta dos decimales.',
            'token.uuid' => 'El identificador de operación no es válido.',
        ];
    }
}
