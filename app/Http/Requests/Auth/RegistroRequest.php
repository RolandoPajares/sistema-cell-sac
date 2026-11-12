<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Define la responsabilidad de la clase RegistroRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RegistroRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'tipo_cliente' con las reglas definidas en la instruccion siguiente.
            'tipo_cliente' => ['required', Rule::in(['minorista', 'mayorista'])],
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120'],
            // Valida el campo 'correo' con las reglas definidas en la instruccion siguiente.
            'correo' => ['required', 'email', 'max:160', Rule::unique('usuarios', 'correo')],
            // Valida el campo 'ruc' con las reglas definidas en la instruccion siguiente.
            'ruc' => ['nullable', 'required_if:tipo_cliente,mayorista', 'digits:11', Rule::unique('clientes', 'documento')],
            // Valida el campo 'password' con las reglas definidas en la instruccion siguiente.
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }
}
