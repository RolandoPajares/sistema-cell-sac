<?php

namespace App\Http\Requests\Compartidos\Catalogo;

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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return true;
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
            // Valida el campo 'q' con las reglas definidas en la instruccion siguiente.
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            // Valida el campo 'categoria' con las reglas definidas en la instruccion siguiente.
            'categoria' => ['sometimes', 'nullable', 'integer', 'exists:categorias,id'],
            // Valida el campo 'marca' con las reglas definidas en la instruccion siguiente.
            'marca' => ['sometimes', 'nullable', 'string', 'max:80'],
            // Valida el campo 'min' con las reglas definidas en la instruccion siguiente.
            'min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            // Valida el campo 'max' con las reglas definidas en la instruccion siguiente.
            'max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            // Valida el campo 'orden' con las reglas definidas en la instruccion siguiente.
            'orden' => ['sometimes', 'nullable', Rule::in(['precio_asc', 'precio_desc', 'nuevo'])],
            // Valida el campo 'per_page' con las reglas definidas en la instruccion siguiente.
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
