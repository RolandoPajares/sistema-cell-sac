<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class GuardarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160', Rule::unique('users', 'correo')],
            'rol' => ['required', Rule::in(User::ROLES)],
            'password' => ['required', Password::min(12)->letters()->numbers()],
            'documento' => ['required_if:rol,cliente_mayorista,cliente_minorista', 'nullable', 'string', 'max:20', Rule::unique('clientes', 'documento')],
            'razon_social' => ['nullable', 'string', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}
