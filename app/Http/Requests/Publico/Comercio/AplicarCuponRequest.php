<?php

namespace App\Http\Requests\Publico\Comercio;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase AplicarCuponRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AplicarCuponRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return ['codigo' => ['required', 'string', 'max:50']];
    }
}
