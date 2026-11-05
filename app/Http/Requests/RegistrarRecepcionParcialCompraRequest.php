<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarRecepcionParcialCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        $compra = $this->route('compra');
        $compraId = is_object($compra) && method_exists($compra, 'getKey') ? $compra->getKey() : $compra;
        $claveSesion = 'compra_recepcion_tokens.'.(is_numeric($compraId) ? (int) $compraId : 0);

        return [
            'token_recepcion' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/', function (string $atributo, mixed $valor, Closure $fallar) use ($claveSesion): void {
                $tokenSesion = $this->session()->get($claveSesion);
                if (! is_string($valor) || ! is_string($tokenSesion) || ! hash_equals($tokenSesion, $valor)) {
                    $fallar('La solicitud de recepción venció; vuelve a abrir la orden.');
                }
            }],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.detalle_compra_id' => ['required', 'integer', 'distinct', 'exists:detalles_compras,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:0', 'max:1000'],
            'items.*.identificadores' => ['nullable', 'string', 'max:30000'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }
}
