<?php

namespace App\Http\Requests\Gestion\Finanzas;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase AnularOperacionFinancieraRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AnularOperacionFinancieraRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['motivo' => ['required', 'string', 'min:10', 'max:500']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Valida el campo 'motivo.required' con las reglas definidas en la instruccion siguiente.
            'motivo.required' => 'Explica el motivo de la anulación.',
            // Valida el campo 'motivo.min' con las reglas definidas en la instruccion siguiente.
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            // Valida el campo 'motivo.max' con las reglas definidas en la instruccion siguiente.
            'motivo.max' => 'El motivo no puede superar 500 caracteres.',
        ];
    }
}
