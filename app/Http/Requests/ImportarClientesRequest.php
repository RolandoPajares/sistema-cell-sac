<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class ImportarClientesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'confirmado' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Selecciona un archivo CSV.',
            'archivo.mimes' => 'La importación acepta únicamente archivos CSV UTF-8.',
            'confirmado.accepted' => 'Confirma que revisaste el archivo antes de importarlo.',
        ];
    }
}
