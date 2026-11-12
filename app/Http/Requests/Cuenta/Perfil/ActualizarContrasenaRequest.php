<?php

namespace App\Http\Requests\Cuenta\Perfil;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Define la responsabilidad de la clase ActualizarContrasenaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarContrasenaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            // Valida el campo 'password_actual' con las reglas definidas en la instruccion siguiente.
            'password_actual' => ['required', 'current_password'],
            // Valida el campo 'password' con las reglas definidas en la instruccion siguiente.
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }
}
