<?php

namespace App\Http\Requests\Gestion\Administracion\Organizacion;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarEmpresaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarEmpresaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
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
