<?php

namespace App\Http\Requests\Inteligente\Cotizador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase OptimizadorInteligenteRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class OptimizadorInteligenteRequest extends FormRequest
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
            'presupuesto' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'objetivo' => ['nullable', Rule::in(['variety', 'units', 'margin', 'premium'])],
        ];
    }
}
