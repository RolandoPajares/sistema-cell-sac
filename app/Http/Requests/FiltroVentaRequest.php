<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
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
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:30'],
            'canal' => ['nullable', Rule::in(['ecommerce', 'presencial'])],
            'cliente_id' => ['nullable', 'integer', 'min:1'],
            'sede_id' => ['nullable', 'integer', 'min:1'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
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
            'buscar.string' => 'La búsqueda debe ser texto.',
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            'estado.string' => 'El estado indicado no es válido.',
            'estado.max' => 'El estado indicado es demasiado largo.',
            'canal.in' => 'Selecciona un canal válido.',
            'cliente_id.integer' => 'Selecciona un cliente válido.',
            'cliente_id.min' => 'Selecciona un cliente válido.',
            'sede_id.integer' => 'Selecciona una sede válida.',
            'sede_id.min' => 'Selecciona una sede válida.',
            'desde.date_format' => 'La fecha inicial debe tener el formato AAAA-MM-DD.',
            'hasta.date_format' => 'La fecha final debe tener el formato AAAA-MM-DD.',
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
