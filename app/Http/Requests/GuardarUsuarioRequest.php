<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class GuardarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.crear');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160', Rule::unique('usuarios', 'correo')],
            'rol' => ['required', Rule::in($this->availableRoles())],
            'sede_id' => ['nullable', 'integer', Rule::exists('sedes', 'id')->where('activa', true)],
            'password' => ['required', Password::min(12)->letters()->numbers()],
            'password_confirmation' => ['required', 'same:password'],
            'documento' => ['required_if:rol,cliente_mayorista,cliente_minorista', 'nullable', 'string', 'max:20', Rule::unique('clientes', 'documento')],
            'razon_social' => ['nullable', 'string', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }

    /** @return array<int,string> */
    private function availableRoles(): array
    {
        if (Schema::hasTable('roles')) {
            $configuredRoles = DB::table('roles')->where('activo', true)->orderBy('codigo')->pluck('codigo')->all();

            return $configuredRoles;
        }

        return [];
    }
}
