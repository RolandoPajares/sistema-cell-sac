<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Transferencias;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Valida una transferencia de existencias y evita repetir producto/variante en sus líneas.
 */
class GuardarTransferenciaExistenciaRequest extends FormRequest
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
            // Valida el campo 'almacen_origen_id' con las reglas definidas en la instruccion siguiente.
            'almacen_origen_id' => ['required', 'integer', 'exists:almacenes,id'],
            // Valida el campo 'almacen_destino_id' con las reglas definidas en la instruccion siguiente.
            'almacen_destino_id' => ['required', 'integer', 'different:almacen_origen_id', 'exists:almacenes,id'],
            // Valida el campo 'observaciones' con las reglas definidas en la instruccion siguiente.
            'observaciones' => ['nullable', 'string', 'max:500'],
            // Valida el campo 'items' con las reglas definidas en la instruccion siguiente.
            'items' => ['required', 'array', 'min:1', 'max:100'],
            // Valida el campo 'items.*.producto_id' con las reglas definidas en la instruccion siguiente.
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            // Valida el campo 'items.*.variante_id' con las reglas definidas en la instruccion siguiente.
            'items.*.variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            // Valida el campo 'items.*.cantidad' con las reglas definidas en la instruccion siguiente.
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    /**
     * Añade validaciones posteriores para impedir transferencias inconsistentes.
     *
     * @param  Validator  $validator  Validador al que se agregan comprobaciones de origen, destino y cantidades.
     * @return void Registra errores adicionales sobre el validador cuando corresponde.
     */
    // Agrega validaciones adicionales despues de aplicar las reglas principales.
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }

            $keys = [];
            foreach ($items as $index => $item) {
                if (! is_array($item) || ! isset($item['producto_id'])) {
                    continue;
                }

                $variantId = isset($item['variante_id']) && $item['variante_id'] !== '' ? (int) $item['variante_id'] : 'null';
                $key = (int) $item['producto_id'].':'.$variantId;
                if (isset($keys[$key])) {
                    $validator->errors()->add('items.'.$index.'.producto_id', 'El producto y su variante no se pueden repetir en la transferencia.');
                }
                $keys[$key] = true;
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'almacen_origen_id' => 'almacen de origen', 'almacen_destino_id' => 'almacen de destino',
            'clave_idempotencia' => 'clave de solicitud',
            'items' => 'productos', 'items.*.producto_id' => 'producto', 'items.*.variante_id' => 'variante',
            'items.*.cantidad' => 'cantidad solicitada', 'observaciones' => 'observaciones',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'almacen_destino_id' cuando falla la regla 'different'.
            'almacen_destino_id.different' => 'El almacen de destino debe ser distinto del almacen de origen.',
            // Muestra el mensaje del campo 'items' cuando falla la regla 'required'.
            'items.required' => 'Agrega al menos un producto a la transferencia.',
            // Muestra el mensaje del campo 'items' cuando falla la regla '*.cantidad.integer'.
            'items.*.cantidad.integer' => 'Las transferencias se registran en unidades enteras.',
        ];
    }
}
