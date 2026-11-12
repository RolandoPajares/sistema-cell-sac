<?php

namespace App\Http\Requests\Gestion\Soporte\Reclamos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarCasoSoporteRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ActualizarCasoSoporteRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.soporte.actualizar');
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
            // Valida el campo 'asunto' con las reglas definidas en la instruccion siguiente.
            'asunto' => ['required', 'string', 'max:180'],
            // Valida el campo 'prioridad' con las reglas definidas en la instruccion siguiente.
            'prioridad' => ['required', Rule::in(['baja', 'normal', 'alta', 'urgente'])],
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['required', Rule::in(['abierto', 'en_proceso', 'resuelto', 'cerrado'])],
            // Valida el campo 'cliente_id' con las reglas definidas en la instruccion siguiente.
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
        ];
    }
}
