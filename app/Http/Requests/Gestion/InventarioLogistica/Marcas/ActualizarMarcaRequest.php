<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Marcas;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarMarcaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:120'], 'descripcion' => ['nullable', 'string', 'max:500'], 'activo' => ['sometimes', 'boolean']];
    }
}
