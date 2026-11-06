<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarDevolucionGarantiaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarDevolucionGarantiaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.devoluciones.crear');
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:40', Rule::unique('devoluciones_garantias', 'codigo')],
            'pedido_id' => ['required', 'integer', 'exists:pedidos,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'tipo' => ['required', Rule::in(['Devolución', 'Garantía', 'Cambio'])],
            'motivo' => ['required', 'string', 'max:5000'],
            'estado' => ['required', Rule::in(['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'])],
            'fecha_solicitud' => ['required', 'date'],
            'fecha_limite' => ['nullable', 'date', 'after_or_equal:fecha_solicitud'],
            'resolucion' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
