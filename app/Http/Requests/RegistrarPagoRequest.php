<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase RegistrarPagoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RegistrarPagoRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'uuid'],
            'origen' => ['required', 'regex:/\\A(?:pedido|venta|compra):[1-9][0-9]*\\z/'],
            'medio_pago_id' => ['required', 'integer', 'exists:medios_pago,id'],
            'sesion_caja_id' => ['nullable', 'integer', 'exists:sesiones_caja,id'],
            'cuenta_bancaria_id' => ['nullable', 'integer', 'exists:cuentas_bancarias,id'],
            'importe' => ['required', 'regex:/\\A(?:0|[1-9][0-9]{0,12})(?:\\.[0-9]{1,2})?\\z/'],
            'referencia' => ['nullable', 'string', 'max:120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'token.required' => 'La solicitud de pago no tiene identificador. Actualiza el formulario.',
            'token.uuid' => 'El identificador de la solicitud no es válido.',
            'origen.required' => 'Selecciona un pedido, venta o compra.',
            'origen.regex' => 'El documento seleccionado no es válido.',
            'medio_pago_id.required' => 'Selecciona un medio de pago.',
            'medio_pago_id.exists' => 'El medio de pago seleccionado no existe.',
            'sesion_caja_id.exists' => 'La sesión de caja seleccionada no existe.',
            'cuenta_bancaria_id.exists' => 'La cuenta bancaria seleccionada no existe.',
            'importe.required' => 'Ingresa el importe.',
            'importe.regex' => 'Ingresa un importe válido con máximo dos decimales.',
            'referencia.max' => 'La referencia no puede superar 120 caracteres.',
        ];
    }
}
