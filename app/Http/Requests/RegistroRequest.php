<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160', Rule::unique('users', 'correo')],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }
}
