<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la consulta enviada al asistente y admite el alias de entrada `message`.
 */
class AsistenteRequest extends FormRequest
{
    /**
     * Normaliza el mensaje del asistente antes de aplicar las reglas de validación.
     *
     * @return void Actualiza los datos internos de la solicitud antes de validar.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('consulta') && $this->filled('message')) {
            $this->merge(['consulta' => $this->input('message')]);
        }
    }

    /**
     * Permite que cualquier usuario llegue a la validación de la consulta del asistente.
     *
     * @return bool Siempre devuelve true; la restricción se aplica mediante las reglas.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Exige una consulta textual de hasta 300 caracteres.
     *
     * @return array<string, list<string>> Reglas aplicadas al campo `consulta`.
     */
    public function rules(): array
    {
        return ['consulta' => ['required', 'string', 'max:300']];
    }
}
