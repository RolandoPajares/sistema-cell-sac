<?php

namespace App\Http\Requests\Gestion\Comercial\Ventas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase GuardarVentaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarVentaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        // Verifica que el usuario esté autenticado y que tenga permisos según el servicio de autorización y la ruta actual.
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */

    //
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // El ID del cliente es obligatorio, debe ser un número entero y existir en la tabla 'clientes'.
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],

            // El tipo de venta es obligatorio y por ahora solo permite el valor 'fisica'.
            'tipo_venta' => ['required', 'in:fisica'],

            // La fecha de la venta es obligatoria y debe tener el formato Año-Mes-Día (YYYY-MM-DD).
            'fecha_venta' => ['required', 'date_format:Y-m-d'],

            // Las observaciones son opcionales, pero si se envían deben ser texto y no superar los 500 caracteres.
            'observaciones' => ['nullable', 'string', 'max:500'],

            // El medio de pago es opcional; si se envía, debe ser un entero existente en la tabla 'medios_pago'.
            'medio_pago_id' => ['nullable', 'integer', 'exists:medios_pago,id'],

            // El importe del pago es opcional; si se envía, debe ser numérico, mayor o igual a 0 y respetar la longitud máxima.
            'importe_pago' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],

            // El token de seguridad de la venta es obligatorio, de tipo string, longitud exacta de 64 caracteres hexadecimales
            // y valida mediante un Closure que coincida exactamente con el token almacenado en la sesión del usuario.
            'venta_token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/', function (string $attribute, mixed $value, Closure $fail): void {
                $token = $this->session()->get('venta_token');
                if (! is_string($value) || ! is_string($token) || ! hash_equals($token, $value)) {
                    $fail('La solicitud de venta ha vencido; vuelve a abrir el formulario.');
                }
            }],

            // 'items' es obligatorio, debe ser un arreglo (array) con un mínimo de 1 elemento y un máximo de 100.
            'items' => ['required', 'array', 'min:1', 'max:100'],

            // Cada producto dentro de los items es obligatorio, entero, no debe repetirse (distinct) y debe existir en la tabla 'productos'.
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],

            // La cantidad de cada producto es obligatoria, entero, con un valor mínimo de 1 y máximo de 1000 unidades.
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
