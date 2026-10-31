<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ActualizarDevolucionGarantiaRequest extends GuardarDevolucionGarantiaRequest
{
    public function rules(): array
    {
        $returnRequest = $this->route('devolucionGarantia');

        return array_replace(parent::rules(), [
            'codigo' => [
                'required',
                'string',
                'max:40',
                Rule::unique('devoluciones_garantias', 'codigo')->ignore($returnRequest),
            ],
        ]);
    }
}
