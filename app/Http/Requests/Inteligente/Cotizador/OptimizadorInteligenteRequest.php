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
            'presupuesto' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            // Valida el campo 'objetivo' con las reglas definidas en la instruccion siguiente.
            'objetivo' => ['nullable', Rule::in(['variety', 'units', 'margin', 'premium'])],
        ];
    }
}
