<?php

namespace App\Http\Requests\Gestion\Finanzas\AperturasCierres;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase RegistrarMovimientoCajaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RegistrarMovimientoCajaRequest extends FormRequest
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

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'direccion' con las reglas definidas en la instruccion siguiente.
            'direccion' => ['required', Rule::in(['entrada', 'salida'])],
            // Valida el campo 'importe' con las reglas definidas en la instruccion siguiente.
            'importe' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
            // Valida el campo 'descripcion' con las reglas definidas en la instruccion siguiente.
            'descripcion' => ['required', 'string', 'max:500'],
            // Valida el campo 'token' con las reglas definidas en la instruccion siguiente.
            'token' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'direccion' cuando falla la regla 'required'.
            'direccion.required' => 'Selecciona entrada o salida.',
            // Muestra el mensaje del campo 'direccion' cuando falla la regla 'in'.
            'direccion.in' => 'La dirección del movimiento no es válida.',
            // Muestra el mensaje del campo 'importe' cuando falla la regla 'required'.
            'importe.required' => 'Ingresa el importe.',
            // Muestra el mensaje del campo 'importe' cuando falla la regla 'regex'.
            'importe.regex' => 'El importe debe tener hasta dos decimales y no ser negativo.',
            // Muestra el mensaje del campo 'descripcion' cuando falla la regla 'required'.
            'descripcion.required' => 'Describe el motivo del movimiento.',
            // Muestra el mensaje del campo 'descripcion' cuando falla la regla 'max'.
            'descripcion.max' => 'La descripción no puede superar 500 caracteres.',
            // Muestra el mensaje del campo 'token' cuando falla la regla 'uuid'.
            'token.uuid' => 'El identificador de operación no es válido.',
        ];
    }
}
