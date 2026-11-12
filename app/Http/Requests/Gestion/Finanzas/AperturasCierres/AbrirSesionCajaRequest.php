<?php

namespace App\Http\Requests\Gestion\Finanzas\AperturasCierres;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
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
            // Valida el campo 'caja_id' con las reglas definidas en la instruccion siguiente.
            'caja_id' => ['required', 'integer', 'exists:cajas,id'],
            // Valida el campo 'saldo_inicial' con las reglas definidas en la instruccion siguiente.
            'saldo_inicial' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
            // Valida el campo 'token' con las reglas definidas en la instruccion siguiente.
            'token' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'caja_id' cuando falla la regla 'required'.
            'caja_id.required' => 'Selecciona una caja.',
            // Muestra el mensaje del campo 'caja_id' cuando falla la regla 'exists'.
            'caja_id.exists' => 'La caja seleccionada no existe.',
            // Muestra el mensaje del campo 'saldo_inicial' cuando falla la regla 'required'.
            'saldo_inicial.required' => 'Ingresa el saldo inicial contado.',
            // Muestra el mensaje del campo 'saldo_inicial' cuando falla la regla 'regex'.
            'saldo_inicial.regex' => 'El saldo inicial debe ser positivo o cero y tener hasta dos decimales.',
            // Muestra el mensaje del campo 'token' cuando falla la regla 'uuid'.
            'token.uuid' => 'El identificador de operación no es válido.',
        ];
    }
}
