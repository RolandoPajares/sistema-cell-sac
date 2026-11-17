<?php

namespace App\Http\Requests\Gestion\Finanzas\AperturasCierres;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase FiltroSesionesCajaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FiltroSesionesCajaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->allowsGlobal($this->user(), 'gestion.seguridad.consultar')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'buscar' con las reglas definidas en la instruccion siguiente.
            'buscar' => ['nullable', 'string', 'max:120'],
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['nullable', Rule::in(['abierta', 'cerrada'])],
            // Valida el campo 'desde' con las reglas definidas en la instruccion siguiente.
            'desde' => ['nullable', 'date_format:Y-m-d'],
            // Valida el campo 'hasta' con las reglas definidas en la instruccion siguiente.
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'buscar' cuando falla la regla 'max'.
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            // Muestra el mensaje del campo 'sede_id' cuando falla la regla 'exists'.
            'sede_id.exists' => 'La sede seleccionada no existe.',
            // Muestra el mensaje del campo 'estado' cuando falla la regla 'in'.
            'estado.in' => 'Selecciona un estado válido.',
            // Muestra el mensaje del campo 'desde' cuando falla la regla 'date_format'.
            'desde.date_format' => 'La fecha inicial debe tener formato AAAA-MM-DD.',
            // Muestra el mensaje del campo 'hasta' cuando falla la regla 'date_format'.
            'hasta.date_format' => 'La fecha final debe tener formato AAAA-MM-DD.',
            // Muestra el mensaje del campo 'hasta' cuando falla la regla 'after_or_equal'.
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }
}
