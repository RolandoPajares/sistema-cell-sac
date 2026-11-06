<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarCajaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarCajaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->hasRole($this->user(), 'administrador')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return [
            'sede_id' => ['required', 'integer', 'exists:sedes,id'],
            'codigo' => ['required', 'string', 'max:40', 'regex:/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/',
                Rule::unique('cajas', 'codigo')->where('sede_id', $this->integer('sede_id'))],
            'nombre' => ['required', 'string', 'max:120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'sede_id.required' => 'Selecciona una sede.',
            'sede_id.exists' => 'La sede seleccionada no existe.',
            'codigo.required' => 'Ingresa el código de caja.',
            'codigo.regex' => 'El código solo admite letras, números, puntos, guiones y guiones bajos.',
            'codigo.unique' => 'Ya existe una caja con ese código en la sede.',
            'codigo.max' => 'El código no puede superar 40 caracteres.',
            'nombre.required' => 'Ingresa el nombre de la caja.',
            'nombre.max' => 'El nombre no puede superar 120 caracteres.',
        ];
    }
}
