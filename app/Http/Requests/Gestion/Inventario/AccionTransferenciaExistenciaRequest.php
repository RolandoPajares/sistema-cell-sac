<?php

namespace App\Http\Requests\Gestion\Inventario;

use App\Servicios\ServicioAutorizacion;
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
            'clave_idempotencia' => ['required', 'uuid'],
            'items' => ['nullable', 'array', 'min:1', 'max:100'],
            'items.*.detalle_id' => ['required', 'integer', 'distinct', 'exists:detalles_transferencias_existencias,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.series' => ['nullable', 'string', 'max:4000'],
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
            'clave_idempotencia.uuid' => 'Actualiza la pagina y vuelve a enviar la operacion.',
            'items.*.detalle_id.distinct' => 'Un detalle solo se puede procesar una vez por operacion.',
            'items.*.cantidad.integer' => 'La cantidad debe expresarse en unidades enteras.',
        ];
    }
}
