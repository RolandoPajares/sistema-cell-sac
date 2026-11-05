<?php

namespace App\Http\Requests\Gestion\Inventario;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarTransferenciaExistenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'clave_idempotencia' => ['required', 'uuid'],
            'almacen_origen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'almacen_destino_id' => ['required', 'integer', 'different:almacen_origen_id', 'exists:almacenes,id'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }

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
            'almacen_destino_id.different' => 'El almacen de destino debe ser distinto del almacen de origen.',
            'items.required' => 'Agrega al menos un producto a la transferencia.',
            'items.*.cantidad.integer' => 'Las transferencias se registran en unidades enteras.',
        ];
    }
}
