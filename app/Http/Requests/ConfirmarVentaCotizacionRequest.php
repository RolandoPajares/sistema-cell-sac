<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmarVentaCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'dispositivos' => ['sometimes', 'array'],
            'dispositivos.*' => ['required', 'array', 'min:1'],
            'dispositivos.*.*' => ['required', 'integer', 'distinct', 'exists:dispositivos,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'dispositivos.*.required' => 'Selecciona los IMEI o números de serie requeridos por cada detalle.',
            'dispositivos.*.*.distinct' => 'No puedes asociar el mismo dispositivo más de una vez a la venta.',
            'dispositivos.*.*.exists' => 'Uno de los IMEI o números de serie ya no existe.',
        ];
    }
}
