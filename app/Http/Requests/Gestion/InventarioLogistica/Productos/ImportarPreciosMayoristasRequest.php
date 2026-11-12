<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Productos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.productos.actualizar');
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
            // Valida el campo 'archivo' con las reglas definidas en la instruccion siguiente.
            'archivo' => ['required', File::types(['csv'])->max(2048)],
            // Valida el campo 'confirmar' con las reglas definidas en la instruccion siguiente.
            'confirmar' => ['accepted'],
        ];
    }
}
