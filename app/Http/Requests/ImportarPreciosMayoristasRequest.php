<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ImportarPreciosMayoristasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(\App\Servicios\ServicioAutorizacion::class)->allows($this->user(), 'gestion.productos.actualizar');
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', File::types(['csv'])->max(2048)],
            'confirmar' => ['accepted'],
        ];
    }
}
