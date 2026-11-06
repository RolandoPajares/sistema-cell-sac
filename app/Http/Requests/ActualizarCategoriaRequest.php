<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarCategoriaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarCategoriaRequest extends GuardarCategoriaRequest
{
    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('categorias', 'nombre')->ignore($category)],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
