<?php

namespace App\Http\Requests\Gestion\Finanzas\AperturasCierres;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase CerrarSesionCajaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CerrarSesionCajaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->allowsGlobal($this->user(), 'gestion.seguridad.consultar')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'saldo_cierre_declarado' con las reglas definidas en la instruccion siguiente.
            'saldo_cierre_declarado' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
            // Valida el campo 'token' con las reglas definidas en la instruccion siguiente.
            'token' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'saldo_cierre_declarado' cuando falla la regla 'required'.
            'saldo_cierre_declarado.required' => 'Ingresa el efectivo contado al cierre.',
            // Muestra el mensaje del campo 'saldo_cierre_declarado' cuando falla la regla 'regex'.
            'saldo_cierre_declarado.regex' => 'El efectivo contado debe ser positivo o cero y tener hasta dos decimales.',
            // Muestra el mensaje del campo 'token' cuando falla la regla 'uuid'.
            'token.uuid' => 'El identificador de operación no es válido.',
        ];
    }
}
