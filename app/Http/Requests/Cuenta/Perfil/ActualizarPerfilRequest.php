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
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160', Rule::unique('usuarios', 'correo')->ignore($this->user())],
        ];

        $user = $this->user();
        if ($user instanceof User) {
            $rules['perfil'] = ['sometimes', 'array'];
            $rules = array_merge($rules, app(ServicioPerfil::class)->editableFieldRules($user));
        }

        return $rules;
    }
}
