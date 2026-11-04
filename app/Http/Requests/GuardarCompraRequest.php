<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class GuardarCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'fecha_compra' => ['required', 'date_format:Y-m-d'],
            'compra_token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/', function (string $attribute, mixed $value, Closure $fail): void {
                $token = $this->session()->get('compra_token');
                if (! is_string($value) || ! is_string($token) || ! hash_equals($token, $value)) {
                    $fail('La solicitud de compra ha vencido; vuelve a abrir el formulario.');
                }
            }],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'items.*.variante_id' => ['nullable', 'integer', 'exists:variantes_productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.costo_unitario' => ['required', 'string', 'regex:/\A\d{1,8}(?:\.\d{1,2})?\z/'],
        ];
    }
}
