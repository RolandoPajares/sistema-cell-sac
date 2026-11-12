<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Almacenes;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase CambiarEstadoAlmacenRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CambiarEstadoAlmacenRequest extends FormRequest
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

        return $usuario instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this)
            && app(ServicioAutorizacion::class)->allows($usuario, 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            // Valida el campo 'activo' con las reglas definidas en la instruccion siguiente.
            'activo' => ['required', 'boolean'],
            // Valida el campo 'motivo' con las reglas definidas en la instruccion siguiente.
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'activo' cuando falla la regla 'required'.
            'activo.required' => 'Selecciona el estado que debe tener el almacén.',
            // Muestra el mensaje del campo 'motivo' cuando falla la regla 'required'.
            'motivo.required' => 'Explica por qué se cambia el estado del almacén.',
            // Muestra el mensaje del campo 'motivo' cuando falla la regla 'min'.
            'motivo.min' => 'El motivo debe contener al menos 10 caracteres.',
        ];
    }
}
