<?php

namespace App\Http\Requests\Cuenta\Perfil;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ActualizarPreferenciasRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarPreferenciasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'menu_compacto' con las reglas definidas en la instruccion siguiente.
            'menu_compacto' => ['sometimes', 'boolean'],
            // Valida el campo 'idioma' con las reglas definidas en la instruccion siguiente.
            'idioma' => ['sometimes', 'required', 'in:es'],
        ];
    }
}
