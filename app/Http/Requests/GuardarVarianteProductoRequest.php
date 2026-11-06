<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarVarianteProductoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarVarianteProductoRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        $reglas = [
            'codigo_sku' => ['nullable', 'string', 'max:80', Rule::unique('variantes_productos', 'codigo_sku')->ignore($this->route('variante'))],
            'codigo_barras' => ['nullable', 'string', 'max:80', Rule::unique('variantes_productos', 'codigo_barras')->ignore($this->route('variante'))],
            'nombre_color' => ['required', 'string', 'max:100'],
            'codigo_color' => ['nullable', 'string', 'max:20'],
            'controla_serie' => ['sometimes', 'boolean'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $reglas['estado'] = ['sometimes', 'string', Rule::in(['activo', 'inactivo'])];
        }

        return $reglas;
    }

    /**
     * Ejecuta la operación prepareForValidation definida por la clase GuardarVarianteProductoRequest.
     *
     * @return void Resultado de la operación.
     */
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
