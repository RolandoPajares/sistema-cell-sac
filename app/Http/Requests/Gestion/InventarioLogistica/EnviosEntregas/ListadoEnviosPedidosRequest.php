<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\EnviosEntregas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ListadoEnviosPedidosRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ListadoEnviosPedidosRequest extends FormRequest
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
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:30', 'exists:envios_pedidos,estado'],
            'modalidad' => ['nullable', 'string', 'max:30', 'exists:envios_pedidos,modalidad'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'buscar' => 'búsqueda',
            'estado' => 'estado',
            'modalidad' => 'modalidad',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede exceder los 100 caracteres.',
            'estado.exists' => 'El estado seleccionado ya no está disponible.',
            'modalidad.exists' => 'La modalidad seleccionada ya no está disponible.',
        ];
    }
}
