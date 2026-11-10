<?php

namespace App\Http\Requests\Gestion\Comercial\Ventas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ConfirmarVentaCotizacionRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ConfirmarVentaCotizacionRequest extends FormRequest
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
            'dispositivos' => ['sometimes', 'array'],
            'dispositivos.*' => ['required', 'array', 'min:1'],
            'dispositivos.*.*' => ['required', 'integer', 'distinct', 'exists:dispositivos,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'dispositivos.*.required' => 'Selecciona los IMEI o números de serie requeridos por cada detalle.',
            'dispositivos.*.*.distinct' => 'No puedes asociar el mismo dispositivo más de una vez a la venta.',
            'dispositivos.*.*.exists' => 'Uno de los IMEI o números de serie ya no existe.',
        ];
    }
}
