<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['minorista', 'mayorista'])],
            'documento' => ['required', 'string', 'max:20', Rule::unique('clientes', 'documento')],
            'razon_social' => ['nullable', 'string', 'max:160'],
            'nombre_contacto' => ['required', 'string', 'max:160'],
            'correo' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}
