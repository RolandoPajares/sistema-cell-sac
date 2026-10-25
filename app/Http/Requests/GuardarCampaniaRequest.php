<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarCampaniaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'marketing'], true);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'ubicacion' => ['required', Rule::in(['emergente', 'lateral', 'barra_superior'])],
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'texto_boton' => ['required', 'string', 'max:80'],
            'url_boton' => ['required', 'string', 'max:255'],
            'url_imagen' => ['nullable', 'string', 'max:255'],
            'precio_anterior' => ['nullable', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lte:precio_anterior'],
            'inicia_en' => ['required', 'date'],
            'finaliza_en' => ['required', 'date', 'after:inicia_en'],
            'segmentos' => ['nullable', 'array'],
            'segmentos.*' => ['integer', 'exists:segmentos_clientes,id'],
        ];
    }
}
