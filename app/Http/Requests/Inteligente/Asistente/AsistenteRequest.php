<?php

namespace App\Http\Requests\Inteligente\Asistente;

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
    // Ajusta o limpia los datos recibidos antes de aplicar las reglas de validacion.
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Exige una consulta textual de hasta 300 caracteres.
     *
     * @return array<string, list<string>> Reglas aplicadas al campo `consulta`.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return ['consulta' => ['required', 'string', 'max:300']];
    }
}
