<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class ListadoDispositivosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:100'],
            'producto' => ['nullable', 'integer', 'exists:productos,id'],
            'almacen' => ['nullable', 'integer', 'exists:almacenes,id'],
            'estado' => ['nullable', 'string', 'max:30', 'exists:dispositivos,estado'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'buscar' => 'búsqueda',
            'producto' => 'producto',
            'almacen' => 'almacén',
            'estado' => 'estado',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede exceder los 100 caracteres.',
            'estado.exists' => 'El estado seleccionado ya no está disponible.',
        ];
    }
}
