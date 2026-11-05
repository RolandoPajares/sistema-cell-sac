<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();
        $autorizacion = app(ServicioAutorizacion::class);

        return $usuario instanceof User
            && $autorizacion->allowsRoute($this)
            && $autorizacion->allows($usuario, 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'activa' => ['required', 'boolean'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'activa.required' => 'Selecciona el estado que debe tener la sede.',
            'motivo.required' => 'Explica por qué se cambia el estado de la sede.',
            'motivo.min' => 'El motivo debe contener al menos 10 caracteres.',
        ];
    }
}
