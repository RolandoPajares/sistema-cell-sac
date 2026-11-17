<?php

namespace App\Http\Requests\Publico\Catalogo;

use Illuminate\Foundation\Http\FormRequest;

class GuardarResenaProductoRequest extends FormRequest
{
    // Decide si la persona que envia la solicitud tiene permiso para ejecutar la operacion.
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    // Define las reglas que deben cumplir los datos recibidos antes de continuar.
    public function rules(): array
    {
        return [
            // Valida el campo 'valoracion' con las reglas definidas en la instruccion siguiente.
            'valoracion' => ['required', 'integer', 'between:1,5'],
            // Valida el campo 'comentario' con las reglas definidas en la instruccion siguiente.
            'comentario' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
