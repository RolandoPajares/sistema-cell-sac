<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class GuardarVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tipo_venta' => ['required', 'in:fisica'],
            'fecha_venta' => ['required', 'date_format:Y-m-d'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'venta_token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/', function (string $attribute, mixed $value, Closure $fail): void {
                $token = $this->session()->get('venta_token');
                if (! is_string($value) || ! is_string($token) || ! hash_equals($token, $value)) {
                    $fail('La solicitud de venta ha vencido; vuelve a abrir el formulario.');
                }
            }],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
