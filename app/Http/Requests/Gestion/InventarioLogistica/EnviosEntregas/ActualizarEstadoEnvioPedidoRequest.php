<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\EnviosEntregas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.inventario.ajustar');
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
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['required', Rule::in(['pendiente', 'preparando', 'despachado', 'en_transito', 'entregado', 'incidencia', 'cancelado'])],
            // Valida el campo 'descripcion' con las reglas definidas en la instruccion siguiente.
            'descripcion' => ['nullable', 'string', 'max:500'],
            // Valida el campo 'identificador_externo' con las reglas definidas en la instruccion siguiente.
            'identificador_externo' => ['nullable', 'string', 'max:160', 'regex:/\A[A-Za-z0-9._:-]+\z/'],
            // Valida el campo 'ocurrido_en' con las reglas definidas en la instruccion siguiente.
            'ocurrido_en' => ['nullable', 'date'],
        ];
    }
}
