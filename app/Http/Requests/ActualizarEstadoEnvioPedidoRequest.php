<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarEstadoEnvioPedidoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ActualizarEstadoEnvioPedidoRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.inventario.ajustar');
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['pendiente', 'preparando', 'despachado', 'en_transito', 'entregado', 'incidencia', 'cancelado'])],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'identificador_externo' => ['nullable', 'string', 'max:160', 'regex:/\A[A-Za-z0-9._:-]+\z/'],
            'ocurrido_en' => ['nullable', 'date'],
        ];
    }
}
