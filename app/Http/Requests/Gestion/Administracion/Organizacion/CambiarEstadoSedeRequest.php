<?php

namespace App\Http\Requests\Gestion\Administracion\Organizacion;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase CambiarEstadoSedeRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CambiarEstadoSedeRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        $usuario = $this->user();
        $autorizacion = app(ServicioAutorizacion::class);

        return $usuario instanceof User
            && $autorizacion->allowsRoute($this)
            && $autorizacion->allows($usuario, 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            // Valida el campo 'activa' con las reglas definidas en la instruccion siguiente.
            'activa' => ['required', 'boolean'],
            // Valida el campo 'motivo' con las reglas definidas en la instruccion siguiente.
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'activa' cuando falla la regla 'required'.
            'activa.required' => 'Selecciona el estado que debe tener la sede.',
            // Muestra el mensaje del campo 'motivo' cuando falla la regla 'required'.
            'motivo.required' => 'Explica por qué se cambia el estado de la sede.',
            // Muestra el mensaje del campo 'motivo' cuando falla la regla 'min'.
            'motivo.min' => 'El motivo debe contener al menos 10 caracteres.',
        ];
    }
}
