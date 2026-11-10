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
            'presupuesto' => ['nullable', 'numeric', 'min:0'],
            'uso' => ['nullable', Rule::in(['gaming', 'camera', 'study', 'work', 'social'])],
            'prioridad' => ['nullable', Rule::in(['rendimiento', 'camara', 'bateria', 'valor'])],
        ];
    }
}
