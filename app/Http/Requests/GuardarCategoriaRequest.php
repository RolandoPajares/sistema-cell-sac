<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('categorias', 'nombre')],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
