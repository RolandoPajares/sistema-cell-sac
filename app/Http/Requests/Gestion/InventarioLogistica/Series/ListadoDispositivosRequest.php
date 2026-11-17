<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Series;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ListadoDispositivosRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ListadoDispositivosRequest extends FormRequest
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
            // Valida el campo 'buscar' con las reglas definidas en la instruccion siguiente.
            'buscar' => ['nullable', 'string', 'max:100'],
            // Valida el campo 'producto' con las reglas definidas en la instruccion siguiente.
            'producto' => ['nullable', 'integer', 'exists:productos,id'],
            // Valida el campo 'almacen' con las reglas definidas en la instruccion siguiente.
            'almacen' => ['nullable', 'integer', 'exists:almacenes,id'],
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['nullable', 'string', 'max:30', 'exists:dispositivos,estado'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'buscar' => 'búsqueda',
            'producto' => 'producto',
            'almacen' => 'almacén',
            'estado' => 'estado',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'buscar' cuando falla la regla 'max'.
            'buscar.max' => 'La búsqueda no puede exceder los 100 caracteres.',
            // Muestra el mensaje del campo 'estado' cuando falla la regla 'exists'.
            'estado.exists' => 'El estado seleccionado ya no está disponible.',
        ];
    }
}
