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
            'producto_id' => ['sometimes', 'integer', 'exists:productos,id'],
            'almacen_id' => ['sometimes', 'required', 'integer', 'exists:almacenes,id'],
            'variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            'tipo_movimiento' => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            'cantidad' => ['required', 'integer', $this->input('tipo_movimiento') === 'ajuste' ? 'min:0' : 'min:1'],
            'notas' => ['required', 'string', 'max:500'],
        ];
    }
}
