<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase AsistenteRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AsistenteRequest extends FormRequest
{
    /**
     * Ejecuta la operación prepareForValidation definida por la clase AsistenteRequest.
     *
     * @return void Resultado de la operación.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('consulta') && $this->filled('message')) {
            $this->merge(['consulta' => $this->input('message')]);
        }
    }

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
        return ['consulta' => ['required', 'string', 'max:300']];
    }
}
