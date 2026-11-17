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
            // Valida el campo 'cantidad' con las reglas definidas en la instruccion siguiente.
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:20'],
            // Valida el campo 'variante_id' con las reglas definidas en la instruccion siguiente.
            'variante_id' => ['nullable', 'integer', 'min:1'],
            // Valida el campo 'comprar_ahora' con las reglas definidas en la instruccion siguiente.
            'comprar_ahora' => ['nullable', 'boolean'],
        ];
    }
}
