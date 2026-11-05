<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class ListadoEnviosPedidosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:30', 'exists:envios_pedidos,estado'],
            'modalidad' => ['nullable', 'string', 'max:30', 'exists:envios_pedidos,modalidad'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'buscar' => 'búsqueda',
            'estado' => 'estado',
            'modalidad' => 'modalidad',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede exceder los 100 caracteres.',
            'estado.exists' => 'El estado seleccionado ya no está disponible.',
            'modalidad.exists' => 'La modalidad seleccionada ya no está disponible.',
        ];
    }
}
