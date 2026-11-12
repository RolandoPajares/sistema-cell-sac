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
            // Valida el campo 'proveedor_id' con las reglas definidas en la instruccion siguiente.
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            // Valida el campo 'fecha_compra' con las reglas definidas en la instruccion siguiente.
            'fecha_compra' => ['required', 'date_format:Y-m-d'],
            // Valida el campo 'numero_referencia' con las reglas definidas en la instruccion siguiente.
            'numero_referencia' => ['nullable', 'string', 'max:100'],
            // Valida el campo 'moneda' con las reglas definidas en la instruccion siguiente.
            'moneda' => ['nullable', 'string', 'size:3', 'in:PEN,USD'],
            // Valida el campo 'compra_token' con las reglas definidas en la instruccion siguiente.
            'compra_token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/', function (string $attribute, mixed $value, Closure $fail): void {
                $token = $this->session()->get('compra_token');
                if (! is_string($value) || ! is_string($token) || ! hash_equals($token, $value)) {
                    $fail('La solicitud de compra ha vencido; vuelve a abrir el formulario.');
                }
            }],
            // Valida el campo 'items' con las reglas definidas en la instruccion siguiente.
            'items' => ['required', 'array', 'min:1', 'max:100'],
            // Valida el campo 'items.*.producto_id' con las reglas definidas en la instruccion siguiente.
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            // Valida el campo 'items.*.variante_id' con las reglas definidas en la instruccion siguiente.
            'items.*.variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            // Valida el campo 'items.*.cantidad' con las reglas definidas en la instruccion siguiente.
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
            // Valida el campo 'items.*.costo_unitario' con las reglas definidas en la instruccion siguiente.
            'items.*.costo_unitario' => ['required', 'string', 'regex:/\A\d{1,8}(?:\.\d{1,2})?\z/'],
            // Valida el campo 'items.*.descuento' con las reglas definidas en la instruccion siguiente.
            'items.*.descuento' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            // Valida el campo 'items.*.impuesto' con las reglas definidas en la instruccion siguiente.
            'items.*.impuesto' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }
}
