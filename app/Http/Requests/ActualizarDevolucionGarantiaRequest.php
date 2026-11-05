<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Validation\Rule;

class ActualizarDevolucionGarantiaRequest extends GuardarDevolucionGarantiaRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.devoluciones.actualizar');
    }

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
