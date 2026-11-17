<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\ExistenciasMovimientos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase AjustarInventarioRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AjustarInventarioRequest extends FormRequest
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
            // Valida el campo 'producto_id' con las reglas definidas en la instruccion siguiente.
            'producto_id' => ['sometimes', 'integer', 'exists:productos,id'],
            // Valida el campo 'almacen_id' con las reglas definidas en la instruccion siguiente.
            'almacen_id' => ['sometimes', 'required', 'integer', 'exists:almacenes,id'],
            // Valida el campo 'variante_id' con las reglas definidas en la instruccion siguiente.
            'variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            // Valida el campo 'tipo_movimiento' con las reglas definidas en la instruccion siguiente.
            'tipo_movimiento' => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            // Valida el campo 'cantidad' con las reglas definidas en la instruccion siguiente.
            'cantidad' => ['required', 'integer', $this->input('tipo_movimiento') === 'ajuste' ? 'min:0' : 'min:1'],
            // Valida el campo 'notas' con las reglas definidas en la instruccion siguiente.
            'notas' => ['required', 'string', 'max:500'],
        ];
    }
}
