<?php

namespace App\Http\Requests\Cuenta\Perfil;

use App\Models\User;
use App\Servicios\Cuenta\Perfil\ServicioPerfil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarPerfilRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarPerfilRequest extends FormRequest
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
        $rules = [
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120'],
            // Valida el campo 'correo' con las reglas definidas en la instruccion siguiente.
            'correo' => ['required', 'email', 'max:160', Rule::unique('usuarios', 'correo')->ignore($this->user())],
            // Valida el campo 'telefono' con las reglas definidas en la instruccion siguiente.
            'telefono' => ['nullable', 'string', 'max:30'],
            // Valida el campo 'documento' con las reglas definidas en la instruccion siguiente.
            'documento' => ['nullable', 'string', 'max:20'],
        ];

        $user = $this->user();
        if ($user instanceof User) {
            $rules['perfil'] = ['sometimes', 'array'];
            $rules = array_merge($rules, app(ServicioPerfil::class)->reglasCamposEditables($user));
        }

        return $rules;
    }
}
