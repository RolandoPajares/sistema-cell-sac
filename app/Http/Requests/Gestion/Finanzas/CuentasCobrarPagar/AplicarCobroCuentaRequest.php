<?php

namespace App\Http\Requests\Gestion\Finanzas\CuentasCobrarPagar;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase AplicarCobroCuentaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AplicarCobroCuentaRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'cuenta_cobrar_id' con las reglas definidas en la instruccion siguiente.
            'cuenta_cobrar_id' => ['required', 'integer', 'exists:cuentas_por_cobrar,id'],
            // Valida el campo 'pago_id' con las reglas definidas en la instruccion siguiente.
            'pago_id' => ['required', 'integer', 'exists:pagos,id'],
            // Valida el campo 'importe' con las reglas definidas en la instruccion siguiente.
            'importe' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
        ];
    }
}
