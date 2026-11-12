<?php

namespace App\Http\Requests\Gestion\Configuracion;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ActualizarIntegracionRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ActualizarIntegracionRequest extends FormRequest
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
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.integraciones.actualizar');
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
            // Valida el campo 'activa' con las reglas definidas en la instruccion siguiente.
            'activa' => ['required', 'boolean'],
        ];
    }
}
