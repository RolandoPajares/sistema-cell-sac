<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Define la responsabilidad de la clase ActualizarUsuarioRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarUsuarioRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.actualizar');
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->getKey() : $user;

        return [
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120'],
            // Valida el campo 'correo' con las reglas definidas en la instruccion siguiente.
            'correo' => ['required', 'email', 'max:160', Rule::unique('usuarios', 'correo')->ignore($userId)],
            // Valida el campo 'password' con las reglas definidas en la instruccion siguiente.
            'password' => ['nullable', Password::min(12)->letters()->numbers(), 'confirmed'],
        ];
    }
}
