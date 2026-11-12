<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Categorias;

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
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120', Rule::unique('categorias', 'nombre')->ignore($category)],
            // Valida el campo 'descripcion' con las reglas definidas en la instruccion siguiente.
            'descripcion' => ['nullable', 'string', 'max:500'],
            // Valida el campo 'activo' con las reglas definidas en la instruccion siguiente.
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
