<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * Define la responsabilidad de la clase ImportarPreciosMayoristasRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ImportarPreciosMayoristasRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.productos.actualizar');
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return [
            'archivo' => ['required', File::types(['csv'])->max(2048)],
            'confirmar' => ['accepted'],
        ];
    }
}
