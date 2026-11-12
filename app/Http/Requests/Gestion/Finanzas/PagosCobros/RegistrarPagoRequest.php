<?php

namespace App\Http\Requests\Gestion\Finanzas\PagosCobros;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'token' con las reglas definidas en la instruccion siguiente.
            'token' => ['required', 'uuid'],
            // Valida el campo 'origen' con las reglas definidas en la instruccion siguiente.
            'origen' => ['required', 'regex:/\\A(?:pedido|venta|compra):[1-9][0-9]*\\z/'],
            // Valida el campo 'medio_pago_id' con las reglas definidas en la instruccion siguiente.
            'medio_pago_id' => ['required', 'integer', 'exists:medios_pago,id'],
            // Valida el campo 'sesion_caja_id' con las reglas definidas en la instruccion siguiente.
            'sesion_caja_id' => ['nullable', 'integer', 'exists:sesiones_caja,id'],
            // Valida el campo 'cuenta_bancaria_id' con las reglas definidas en la instruccion siguiente.
            'cuenta_bancaria_id' => ['nullable', 'integer', 'exists:cuentas_bancarias,id'],
            // Valida el campo 'importe' con las reglas definidas en la instruccion siguiente.
            'importe' => ['required', 'regex:/\\A(?:0|[1-9][0-9]{0,12})(?:\\.[0-9]{1,2})?\\z/'],
            // Valida el campo 'referencia' con las reglas definidas en la instruccion siguiente.
            'referencia' => ['nullable', 'string', 'max:120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'token' cuando falla la regla 'required'.
            'token.required' => 'La solicitud de pago no tiene identificador. Actualiza el formulario.',
            // Muestra el mensaje del campo 'token' cuando falla la regla 'uuid'.
            'token.uuid' => 'El identificador de la solicitud no es válido.',
            // Muestra el mensaje del campo 'origen' cuando falla la regla 'required'.
            'origen.required' => 'Selecciona un pedido, venta o compra.',
            // Muestra el mensaje del campo 'origen' cuando falla la regla 'regex'.
            'origen.regex' => 'El documento seleccionado no es válido.',
            // Muestra el mensaje del campo 'medio_pago_id' cuando falla la regla 'required'.
            'medio_pago_id.required' => 'Selecciona un medio de pago.',
            // Muestra el mensaje del campo 'medio_pago_id' cuando falla la regla 'exists'.
            'medio_pago_id.exists' => 'El medio de pago seleccionado no existe.',
            // Muestra el mensaje del campo 'sesion_caja_id' cuando falla la regla 'exists'.
            'sesion_caja_id.exists' => 'La sesión de caja seleccionada no existe.',
            // Muestra el mensaje del campo 'cuenta_bancaria_id' cuando falla la regla 'exists'.
            'cuenta_bancaria_id.exists' => 'La cuenta bancaria seleccionada no existe.',
            // Muestra el mensaje del campo 'importe' cuando falla la regla 'required'.
            'importe.required' => 'Ingresa el importe.',
            // Muestra el mensaje del campo 'importe' cuando falla la regla 'regex'.
            'importe.regex' => 'Ingresa un importe válido con máximo dos decimales.',
            // Muestra el mensaje del campo 'referencia' cuando falla la regla 'max'.
            'referencia.max' => 'La referencia no puede superar 120 caracteres.',
        ];
    }
}
