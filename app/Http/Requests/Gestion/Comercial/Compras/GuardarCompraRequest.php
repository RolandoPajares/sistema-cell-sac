<?php

namespace App\Http\Requests\Gestion\Comercial\Compras;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase GuardarCompraRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarCompraRequest extends FormRequest
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
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_compra' => ['required', 'date_format:Y-m-d'],
            'numero_referencia' => ['nullable', 'string', 'max:100'],
            'moneda' => ['nullable', 'string', 'size:3', 'in:PEN,USD'],
            'compra_token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/', function (string $attribute, mixed $value, Closure $fail): void {
                $token = $this->session()->get('compra_token');
                if (! is_string($value) || ! is_string($token) || ! hash_equals($token, $value)) {
                    $fail('La solicitud de compra ha vencido; vuelve a abrir el formulario.');
                }
            }],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'items.*.variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.costo_unitario' => ['required', 'string', 'regex:/\A\d{1,8}(?:\.\d{1,2})?\z/'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.impuesto' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }
}
