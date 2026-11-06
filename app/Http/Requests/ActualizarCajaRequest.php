<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarCajaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarCajaRequest extends GuardarCajaRequest
{
    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        $box = $this->route('caja');

        return array_replace(parent::rules(), [
            'sede_id' => ['required', 'integer', Rule::in([(int) $box->sede_id])],
            'codigo' => ['required', 'string', 'max:40', 'regex:/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/',
                Rule::unique('cajas', 'codigo')->where('sede_id', $box->sede_id)->ignore($box)],
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + ['sede_id.in' => 'La sede de una caja existente no puede cambiarse.'];
    }
}
