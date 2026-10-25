<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'marca' => ['required', 'string', 'max:80'],
            'nombre' => ['required', 'string', 'max:160'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'precio' => ['required', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lte:precio'],
            'url_imagen' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'existencias' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
