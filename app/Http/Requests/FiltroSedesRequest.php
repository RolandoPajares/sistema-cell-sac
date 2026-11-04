<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltroSedesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();
        $autorizacion = app(ServicioAutorizacion::class);

        return $usuario instanceof User
            && $autorizacion->allowsRoute($this)
            && $autorizacion->allows($usuario, 'gestion.organizacion.consultar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', Rule::in(['activa', 'inactiva'])],
        ];
    }
}
