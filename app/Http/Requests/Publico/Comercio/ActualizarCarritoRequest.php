<?php

namespace App\Http\Requests\Publico\Comercio;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ActualizarCarritoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarCarritoRequest extends FormRequest
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
        return ['cantidad' => ['required', 'integer', 'min:0', 'max:100']];
    }
}
