<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class GuardarImagenVarianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'imagen_principal' => ['sometimes', 'boolean'],
        ];
    }
}
