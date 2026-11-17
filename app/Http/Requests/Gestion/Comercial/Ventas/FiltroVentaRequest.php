<?php

namespace App\Http\Requests\Gestion\Comercial\Ventas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase FiltroVentaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FiltroVentaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
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
            // Valida el campo 'buscar' con las reglas definidas en la instruccion siguiente.
            'buscar' => ['nullable', 'string', 'max:120'],
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['nullable', 'string', 'max:30'],
            // Valida el campo 'canal' con las reglas definidas en la instruccion siguiente.
            'canal' => ['nullable', Rule::in(['ecommerce', 'presencial'])],
            // Valida el campo 'cliente_id' con las reglas definidas en la instruccion siguiente.
            'cliente_id' => ['nullable', 'integer', 'min:1'],
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['nullable', 'integer', 'min:1'],
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
            'buscar' => 'búsqueda', 'estado' => 'estado', 'canal' => 'canal', 'cliente_id' => 'cliente',
            'sede_id' => 'sede', 'desde' => 'fecha inicial', 'hasta' => 'fecha final',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'buscar' cuando falla la regla 'string'.
            'buscar.string' => 'La búsqueda debe ser texto.',
            // Muestra el mensaje del campo 'buscar' cuando falla la regla 'max'.
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            // Muestra el mensaje del campo 'estado' cuando falla la regla 'string'.
            'estado.string' => 'El estado indicado no es válido.',
            // Muestra el mensaje del campo 'estado' cuando falla la regla 'max'.
            'estado.max' => 'El estado indicado es demasiado largo.',
            // Muestra el mensaje del campo 'canal' cuando falla la regla 'in'.
            'canal.in' => 'Selecciona un canal válido.',
            // Muestra el mensaje del campo 'cliente_id' cuando falla la regla 'integer'.
            'cliente_id.integer' => 'Selecciona un cliente válido.',
            // Muestra el mensaje del campo 'cliente_id' cuando falla la regla 'min'.
            'cliente_id.min' => 'Selecciona un cliente válido.',
            // Muestra el mensaje del campo 'sede_id' cuando falla la regla 'integer'.
            'sede_id.integer' => 'Selecciona una sede válida.',
            // Muestra el mensaje del campo 'sede_id' cuando falla la regla 'min'.
            'sede_id.min' => 'Selecciona una sede válida.',
            // Muestra el mensaje del campo 'desde' cuando falla la regla 'date_format'.
            'desde.date_format' => 'La fecha inicial debe tener el formato AAAA-MM-DD.',
            // Muestra el mensaje del campo 'hasta' cuando falla la regla 'date_format'.
            'hasta.date_format' => 'La fecha final debe tener el formato AAAA-MM-DD.',
            // Muestra el mensaje del campo 'hasta' cuando falla la regla 'after_or_equal'.
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
