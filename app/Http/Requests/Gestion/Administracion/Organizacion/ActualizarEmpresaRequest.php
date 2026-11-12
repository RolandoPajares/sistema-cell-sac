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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
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
            // Valida el campo 'razon_social' con las reglas definidas en la instruccion siguiente.
            'razon_social' => ['required', 'string', 'max:180'],
            // Valida el campo 'nombre_comercial' con las reglas definidas en la instruccion siguiente.
            'nombre_comercial' => ['nullable', 'string', 'max:180'],
            // Valida el campo 'ruc' con las reglas definidas en la instruccion siguiente.
            'ruc' => [
                'nullable', 'string', 'size:11', 'regex:/^[0-9]{11}$/',
                Rule::unique('empresas', 'ruc')->ignore($empresaId),
            ],
            // Valida el campo 'moneda_base' con las reglas definidas en la instruccion siguiente.
            'moneda_base' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => ['prohibited'],
            // Valida el campo 'activo' con las reglas definidas en la instruccion siguiente.
            'activo' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Informa que la razon social es obligatoria cuando no fue enviada.
            'razon_social.required' => 'La razón social es obligatoria.',
            // Informa que la razon social supera el limite permitido de caracteres.
            'razon_social.max' => 'La razón social no puede superar 180 caracteres.',
            // Informa que el nombre comercial supera el limite permitido de caracteres.
            'nombre_comercial.max' => 'El nombre comercial no puede superar 180 caracteres.',
            // Informa que el RUC no tiene exactamente once digitos.
            'ruc.size' => 'El RUC debe contener exactamente 11 dígitos.',
            // Informa que el RUC contiene caracteres distintos de digitos.
            'ruc.regex' => 'El RUC solo puede contener 11 dígitos.',
            // Informa que el RUC ya existe en otra empresa.
            'ruc.unique' => 'Ese RUC ya está registrado en otra empresa.',
            // Informa que el codigo de moneda no tiene tres letras.
            'moneda_base.size' => 'La moneda base debe ser un código de tres letras mayúsculas.',
            // Informa que el codigo de moneda no cumple el formato de letras mayusculas.
            'moneda_base.regex' => 'La moneda base debe ser un código de tres letras mayúsculas.',
        ];
    }
}
