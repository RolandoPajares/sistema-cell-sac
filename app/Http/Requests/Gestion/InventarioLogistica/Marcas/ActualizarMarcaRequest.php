<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Marcas;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarMarcaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:120'], 'descripcion' => ['nullable', 'string', 'max:500'], 'activo' => ['sometimes', 'boolean']];
    }
}
