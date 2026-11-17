<?php

namespace App\Http\Requests\Gestion\Finanzas\Reembolsos;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase AprobarReembolsoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AprobarReembolsoRequest extends FormRequest
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

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return ['confirmacion' => ['required', Rule::in(['aprobar'])]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Valida el campo 'confirmacion.required' con las reglas definidas en la instruccion siguiente.
            'confirmacion.required' => 'Confirma la aprobación antes de continuar.',
            // Valida el campo 'confirmacion.in' con las reglas definidas en la instruccion siguiente.
            'confirmacion.in' => 'La confirmación no es válida.',
        ];
    }
}
