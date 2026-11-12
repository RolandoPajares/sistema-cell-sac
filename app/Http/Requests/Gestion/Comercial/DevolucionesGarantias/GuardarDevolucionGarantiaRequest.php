<?php

namespace App\Http\Requests\Gestion\Comercial\DevolucionesGarantias;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
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
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => ['required', 'string', 'max:40', Rule::unique('devoluciones_garantias', 'codigo')],
            // Valida el campo 'pedido_id' con las reglas definidas en la instruccion siguiente.
            'pedido_id' => ['required', 'integer', 'exists:pedidos,id'],
            // Valida el campo 'cliente_id' con las reglas definidas en la instruccion siguiente.
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            // Valida el campo 'producto_id' con las reglas definidas en la instruccion siguiente.
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            // Valida el campo 'tipo' con las reglas definidas en la instruccion siguiente.
            'tipo' => ['required', Rule::in(['Devolución', 'Garantía', 'Cambio'])],
            // Valida el campo 'motivo' con las reglas definidas en la instruccion siguiente.
            'motivo' => ['required', 'string', 'max:5000'],
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['required', Rule::in(['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'])],
            // Valida el campo 'fecha_solicitud' con las reglas definidas en la instruccion siguiente.
            'fecha_solicitud' => ['required', 'date'],
            // Valida el campo 'fecha_limite' con las reglas definidas en la instruccion siguiente.
            'fecha_limite' => ['nullable', 'date', 'after_or_equal:fecha_solicitud'],
            // Valida el campo 'resolucion' con las reglas definidas en la instruccion siguiente.
            'resolucion' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
