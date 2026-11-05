<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarEmpresaRequest extends FormRequest
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
        $empresaId = app(ServicioAutorizacion::class)->activeCompanyId();

        return [
            'razon_social' => ['required', 'string', 'max:180'],
            'nombre_comercial' => ['nullable', 'string', 'max:180'],
            'ruc' => [
                'nullable', 'string', 'size:11', 'regex:/^[0-9]{11}$/',
                Rule::unique('empresas', 'ruc')->ignore($empresaId),
            ],
            'moneda_base' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'codigo' => ['prohibited'],
            'activo' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'razon_social.required' => 'La razón social es obligatoria.',
            'razon_social.max' => 'La razón social no puede superar 180 caracteres.',
            'nombre_comercial.max' => 'El nombre comercial no puede superar 180 caracteres.',
            'ruc.size' => 'El RUC debe contener exactamente 11 dígitos.',
            'ruc.regex' => 'El RUC solo puede contener 11 dígitos.',
            'ruc.unique' => 'Ese RUC ya está registrado en otra empresa.',
            'moneda_base.size' => 'La moneda base debe ser un código de tres letras mayúsculas.',
            'moneda_base.regex' => 'La moneda base debe ser un código de tres letras mayúsculas.',
        ];
    }
}
