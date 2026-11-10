<?php

namespace App\Http\Requests\Publico\Catalogo;

use Illuminate\Foundation\Http\FormRequest;

class GuardarResenaProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'valoracion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
