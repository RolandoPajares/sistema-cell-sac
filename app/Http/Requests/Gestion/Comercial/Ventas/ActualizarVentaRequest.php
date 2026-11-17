<?php

namespace App\Http\Requests\Gestion\Comercial\Ventas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ActualizarVentaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarVentaRequest extends FormRequest
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
            // Valida el campo 'cliente_id' con las reglas definidas en la instruccion siguiente.
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            // Valida el campo 'fecha_venta' con las reglas definidas en la instruccion siguiente.
            'fecha_venta' => ['required', 'date_format:Y-m-d'],
            // Valida el campo 'observaciones' con las reglas definidas en la instruccion siguiente.
            'observaciones' => ['nullable', 'string', 'max:500'],
            // Valida el campo 'items' con las reglas definidas en la instruccion siguiente.
            'items' => ['required', 'array', 'min:1', 'max:100'],
            // Valida el campo 'items.*.producto_id' con las reglas definidas en la instruccion siguiente.
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            // Valida el campo 'items.*.cantidad' con las reglas definidas en la instruccion siguiente.
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
