<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Productos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida los datos de una variante de producto y normaliza sus campos opcionales.
 */
class GuardarVarianteProductoRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        $reglas = [
            // Valida el campo 'codigo_sku' con las reglas definidas en la instruccion siguiente.
            'codigo_sku' => ['nullable', 'string', 'max:80', Rule::unique('variantes_productos', 'codigo_sku')->ignore($this->route('variante'))],
            // Valida el campo 'codigo_barras' con las reglas definidas en la instruccion siguiente.
            'codigo_barras' => ['nullable', 'string', 'max:80', Rule::unique('variantes_productos', 'codigo_barras')->ignore($this->route('variante'))],
            // Valida el campo 'nombre_color' con las reglas definidas en la instruccion siguiente.
            'nombre_color' => ['required', 'string', 'max:100'],
            // Valida el campo 'codigo_color' con las reglas definidas en la instruccion siguiente.
            'codigo_color' => ['nullable', 'string', 'max:20'],
            // Valida el campo 'controla_serie' con las reglas definidas en la instruccion siguiente.
            'controla_serie' => ['sometimes', 'boolean'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $reglas['estado'] = ['sometimes', 'string', Rule::in(['activo', 'inactivo'])];
        }

        return $reglas;
    }

    /**
     * Convierte los campos opcionales de la variante al formato que esperan las reglas.
     *
     * @return void Fusiona los valores normalizados en los datos de la solicitud.
     */
    // Ajusta o limpia los datos recibidos antes de aplicar las reglas de validacion.
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
