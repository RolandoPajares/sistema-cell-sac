<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('categorias', 'nombre')->ignore($category)],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }
}
