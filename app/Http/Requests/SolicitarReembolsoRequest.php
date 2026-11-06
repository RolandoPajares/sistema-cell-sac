<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase SolicitarReembolsoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class SolicitarReembolsoRequest extends FormRequest
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
            'devolucion_id' => ['required', 'integer', 'exists:devoluciones_garantias,id'],
            'pago_id' => ['required', 'integer', 'exists:pagos,id'],
            'importe' => ['required', 'regex:/\\A(?:0|[1-9][0-9]{0,12})(?:\\.[0-9]{1,2})?\\z/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'devolucion_id.required' => 'Selecciona una devolución resuelta.',
            'devolucion_id.exists' => 'La devolución seleccionada no existe.',
            'pago_id.required' => 'Selecciona el cobro original.',
            'pago_id.exists' => 'El cobro seleccionado no existe.',
            'importe.required' => 'Ingresa el importe solicitado.',
            'importe.regex' => 'Ingresa un importe válido con máximo dos decimales.',
        ];
    }
}
