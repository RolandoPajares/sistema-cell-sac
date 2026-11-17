<?php

namespace App\Http\Requests\Inteligente\Comprador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase RecomendacionRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RecomendacionRequest extends FormRequest
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
            // Valida el campo 'presupuesto' con las reglas definidas en la instruccion siguiente.
            'presupuesto' => ['nullable', 'numeric', 'min:0'],
            // Valida el campo 'uso' con las reglas definidas en la instruccion siguiente.
            'uso' => ['nullable', Rule::in(['gaming', 'camera', 'study', 'work', 'social'])],
            // Valida el campo 'prioridad' con las reglas definidas en la instruccion siguiente.
            'prioridad' => ['nullable', Rule::in(['rendimiento', 'camara', 'bateria', 'valor'])],
        ];
    }
}
