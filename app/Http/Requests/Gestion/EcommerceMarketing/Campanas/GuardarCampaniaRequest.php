<?php

namespace App\Http\Requests\Gestion\EcommerceMarketing\Campanas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarCampaniaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarCampaniaRequest extends FormRequest
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
        return [
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120'],
            // Valida el campo 'ubicacion' con las reglas definidas en la instruccion siguiente.
            'ubicacion' => ['required', Rule::in(['emergente', 'lateral', 'barra_superior'])],
            // Valida el campo 'titulo' con las reglas definidas en la instruccion siguiente.
            'titulo' => ['required', 'string', 'max:160'],
            // Valida el campo 'descripcion' con las reglas definidas en la instruccion siguiente.
            'descripcion' => ['nullable', 'string', 'max:500'],
            // Valida el campo 'texto_boton' con las reglas definidas en la instruccion siguiente.
            'texto_boton' => ['required', 'string', 'max:80'],
            // Valida el campo 'url_boton' con las reglas definidas en la instruccion siguiente.
            'url_boton' => ['required', 'string', 'max:255'],
            // Valida el campo 'url_imagen' con las reglas definidas en la instruccion siguiente.
            'url_imagen' => ['nullable', 'string', 'max:255'],
            // Valida el campo 'imagen' con las reglas definidas en la instruccion siguiente.
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // Valida el campo 'precio_anterior' con las reglas definidas en la instruccion siguiente.
            'precio_anterior' => ['nullable', 'numeric', 'min:0'],
            // Valida el campo 'precio_oferta' con las reglas definidas en la instruccion siguiente.
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lte:precio_anterior'],
            // Valida el campo 'inicia_en' con las reglas definidas en la instruccion siguiente.
            'inicia_en' => ['required', 'date'],
            // Valida el campo 'finaliza_en' con las reglas definidas en la instruccion siguiente.
            'finaliza_en' => ['required', 'date', 'after:inicia_en'],
            // Valida el campo 'activo' con las reglas definidas en la instruccion siguiente.
            'activo' => ['sometimes', 'boolean'],
            // Valida el campo 'segmentos' con las reglas definidas en la instruccion siguiente.
            'segmentos' => ['nullable', 'array'],
            // Valida el campo 'segmentos.*' con las reglas definidas en la instruccion siguiente.
            'segmentos.*' => ['integer', 'exists:segmentos_clientes,id'],
        ];
    }
}
