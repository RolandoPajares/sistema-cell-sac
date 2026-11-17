<?php

namespace App\Http\Requests\Gestion\Finanzas\PagosCobros;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ConfirmarPagoEfectivoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ConfirmarPagoEfectivoRequest extends FormRequest
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
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.consultar')
            && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'importe_contado' con las reglas definidas en la instruccion siguiente.
            'importe_contado' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
            // Valida el campo 'observacion' con las reglas definidas en la instruccion siguiente.
            'observacion' => ['required', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'importe_contado' cuando falla la regla 'required'.
            'importe_contado.required' => 'Declara el efectivo contado antes de confirmar.',
            // Muestra el mensaje del campo 'importe_contado' cuando falla la regla 'regex'.
            'importe_contado.regex' => 'El efectivo contado debe tener hasta dos decimales.',
            // Muestra el mensaje del campo 'observacion' cuando falla la regla 'required'.
            'observacion.required' => 'Documenta la verificación del efectivo.',
            // Muestra el mensaje del campo 'observacion' cuando falla la regla 'max'.
            'observacion.max' => 'La observación no puede superar 500 caracteres.',
        ];
    }
}
