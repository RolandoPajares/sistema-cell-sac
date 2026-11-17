<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Transferencias;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase AccionTransferenciaExistenciaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AccionTransferenciaExistenciaRequest extends FormRequest
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
            // Valida el campo 'clave_idempotencia' con las reglas definidas en la instruccion siguiente.
            'clave_idempotencia' => ['required', 'uuid'],
            // Valida el campo 'items' con las reglas definidas en la instruccion siguiente.
            'items' => ['nullable', 'array', 'min:1', 'max:100'],
            // Valida el campo 'items.*.detalle_id' con las reglas definidas en la instruccion siguiente.
            'items.*.detalle_id' => ['required', 'integer', 'distinct', 'exists:detalles_transferencias_existencias,id'],
            // Valida el campo 'items.*.cantidad' con las reglas definidas en la instruccion siguiente.
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            // Valida el campo 'items.*.series' con las reglas definidas en la instruccion siguiente.
            'items.*.series' => ['nullable', 'string', 'max:4000'],
            // Valida el campo 'motivo' con las reglas definidas en la instruccion siguiente.
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'clave_idempotencia' => 'clave de operacion', 'items.*.detalle_id' => 'detalle de transferencia',
            'items.*.cantidad' => 'cantidad', 'items.*.series' => 'IMEI o numeros de serie', 'motivo' => 'motivo',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'clave_idempotencia' cuando falla la regla 'uuid'.
            'clave_idempotencia.uuid' => 'Actualiza la pagina y vuelve a enviar la operacion.',
            // Muestra el mensaje del campo 'items' cuando falla la regla '*.detalle_id.distinct'.
            'items.*.detalle_id.distinct' => 'Un detalle solo se puede procesar una vez por operacion.',
            // Muestra el mensaje del campo 'items' cuando falla la regla '*.cantidad.integer'.
            'items.*.cantidad.integer' => 'La cantidad debe expresarse en unidades enteras.',
        ];
    }
}
