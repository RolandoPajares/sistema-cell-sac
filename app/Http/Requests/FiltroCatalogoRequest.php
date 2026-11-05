<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltroCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'categoria' => ['sometimes', 'nullable', 'integer', 'exists:categorias,id'],
            'marca' => ['sometimes', 'nullable', 'string', 'max:80'],
            'min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'orden' => ['sometimes', 'nullable', Rule::in(['precio_asc', 'precio_desc', 'nuevo'])],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
