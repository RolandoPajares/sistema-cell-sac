<?php

namespace App\Http\Requests\Gestion\Finanzas\Reembolsos;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
            // Valida el campo 'devolucion_id' con las reglas definidas en la instruccion siguiente.
            'devolucion_id' => ['required', 'integer', 'exists:devoluciones_garantias,id'],
            // Valida el campo 'pago_id' con las reglas definidas en la instruccion siguiente.
            'pago_id' => ['required', 'integer', 'exists:pagos,id'],
            // Valida el campo 'importe' con las reglas definidas en la instruccion siguiente.
            'importe' => ['required', 'regex:/\\A(?:0|[1-9][0-9]{0,12})(?:\\.[0-9]{1,2})?\\z/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'devolucion_id' cuando falla la regla 'required'.
            'devolucion_id.required' => 'Selecciona una devolución resuelta.',
            // Muestra el mensaje del campo 'devolucion_id' cuando falla la regla 'exists'.
            'devolucion_id.exists' => 'La devolución seleccionada no existe.',
            // Muestra el mensaje del campo 'pago_id' cuando falla la regla 'required'.
            'pago_id.required' => 'Selecciona el cobro original.',
            // Muestra el mensaje del campo 'pago_id' cuando falla la regla 'exists'.
            'pago_id.exists' => 'El cobro seleccionado no existe.',
            // Muestra el mensaje del campo 'importe' cuando falla la regla 'required'.
            'importe.required' => 'Ingresa el importe solicitado.',
            // Muestra el mensaje del campo 'importe' cuando falla la regla 'regex'.
            'importe.regex' => 'Ingresa un importe válido con máximo dos decimales.',
        ];
    }
}
