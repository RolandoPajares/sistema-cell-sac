<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase FiltroCatalogoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FiltroCatalogoRequest extends FormRequest
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
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'categoria' => ['sometimes', 'nullable', 'integer', 'exists:categorias,id'],
            'marca' => ['sometimes', 'nullable', 'string', 'max:80'],
            'min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'orden' => ['sometimes', 'nullable', Rule::in(['precio_asc', 'precio_desc', 'nuevo'])],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
