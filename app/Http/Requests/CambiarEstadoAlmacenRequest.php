<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoAlmacenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();

        return $usuario instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this)
            && app(ServicioAutorizacion::class)->allows($usuario, 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'activo' => ['required', 'boolean'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'activo.required' => 'Selecciona el estado que debe tener el almacén.',
            'motivo.required' => 'Explica por qué se cambia el estado del almacén.',
            'motivo.min' => 'El motivo debe contener al menos 10 caracteres.',
        ];
    }
}
