<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoCajaRequest extends FormRequest
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
        return ['activa' => ['required', 'boolean']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'activa.required' => 'Indica el estado de la caja.',
            'activa.boolean' => 'El estado de la caja no es válido.',
        ];
    }
}
