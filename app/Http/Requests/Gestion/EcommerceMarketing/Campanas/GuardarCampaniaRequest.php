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
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'ubicacion' => ['required', Rule::in(['emergente', 'lateral', 'barra_superior'])],
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'texto_boton' => ['required', 'string', 'max:80'],
            'url_boton' => ['required', 'string', 'max:255'],
            'url_imagen' => ['nullable', 'string', 'max:255'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'precio_anterior' => ['nullable', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lte:precio_anterior'],
            'inicia_en' => ['required', 'date'],
            'finaliza_en' => ['required', 'date', 'after:inicia_en'],
            'activo' => ['sometimes', 'boolean'],
            'segmentos' => ['nullable', 'array'],
            'segmentos.*' => ['integer', 'exists:segmentos_clientes,id'],
        ];
    }
}
