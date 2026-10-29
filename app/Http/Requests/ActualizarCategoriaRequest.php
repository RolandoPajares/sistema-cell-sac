<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ActualizarCategoriaRequest extends GuardarCategoriaRequest
{
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('categorias', 'nombre')->ignore($category)],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
