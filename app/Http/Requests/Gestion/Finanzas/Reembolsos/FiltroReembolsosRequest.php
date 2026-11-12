<?php

namespace App\Http\Requests\Gestion\Finanzas\Reembolsos;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase FiltroReembolsosRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FiltroReembolsosRequest extends FormRequest
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
        return [
            // Valida el campo 'buscar' con las reglas definidas en la instruccion siguiente.
            'buscar' => ['nullable', 'string', 'max:120'],
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['nullable', 'string', 'max:30'],
            // Valida el campo 'desde' con las reglas definidas en la instruccion siguiente.
            'desde' => ['nullable', 'date_format:Y-m-d'],
            // Valida el campo 'hasta' con las reglas definidas en la instruccion siguiente.
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'buscar' => 'búsqueda',
            'estado' => 'estado',
            'desde' => 'fecha inicial',
            'hasta' => 'fecha final',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'buscar' cuando falla la regla 'max'.
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            // Muestra el mensaje del campo 'estado' cuando falla la regla 'max'.
            'estado.max' => 'El estado no puede superar 30 caracteres.',
            // Muestra el mensaje del campo 'desde' cuando falla la regla 'date_format'.
            'desde.date_format' => 'La fecha inicial debe tener el formato AAAA-MM-DD.',
            // Muestra el mensaje del campo 'hasta' cuando falla la regla 'date_format'.
            'hasta.date_format' => 'La fecha final debe tener el formato AAAA-MM-DD.',
            // Muestra el mensaje del campo 'hasta' cuando falla la regla 'after_or_equal'.
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
