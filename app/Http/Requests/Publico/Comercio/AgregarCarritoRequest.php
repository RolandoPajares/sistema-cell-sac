<?php

namespace App\Http\Requests\Publico\Comercio;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase AgregarCarritoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AgregarCarritoRequest extends FormRequest
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
        return [
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:20'],
            'variante_id' => ['nullable', 'integer', 'min:1'],
            'comprar_ahora' => ['nullable', 'boolean'],
        ];
    }
}
