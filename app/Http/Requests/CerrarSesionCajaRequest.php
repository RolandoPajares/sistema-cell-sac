<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class CerrarSesionCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->hasRole($this->user(), 'administrador')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'saldo_cierre_declarado' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
            'token' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'saldo_cierre_declarado.required' => 'Ingresa el efectivo contado al cierre.',
            'saldo_cierre_declarado.regex' => 'El efectivo contado debe ser positivo o cero y tener hasta dos decimales.',
            'token.uuid' => 'El identificador de operación no es válido.',
        ];
    }
}
