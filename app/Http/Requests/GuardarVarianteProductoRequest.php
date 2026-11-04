<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarVarianteProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        $reglas = [
            'codigo_sku' => ['nullable', 'string', 'max:80', Rule::unique('variantes_productos', 'codigo_sku')->ignore($this->route('variante'))],
            'codigo_barras' => ['nullable', 'string', 'max:80', Rule::unique('variantes_productos', 'codigo_barras')->ignore($this->route('variante'))],
            'nombre_color' => ['required', 'string', 'max:100'],
            'codigo_color' => ['nullable', 'string', 'max:20'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $reglas['estado'] = ['sometimes', 'string', Rule::in(['activo', 'inactivo'])];
        }

        return $reglas;
    }

    protected function prepareForValidation(): void
    {
        $camposOpcionales = [];

        foreach (['codigo_sku', 'codigo_barras', 'codigo_color'] as $campo) {
            $valor = $this->input($campo);
            if (is_string($valor)) {
                $valor = trim($valor);
                $camposOpcionales[$campo] = $valor === '' ? null : $valor;
            }
        }

        $this->merge($camposOpcionales);
    }
}
