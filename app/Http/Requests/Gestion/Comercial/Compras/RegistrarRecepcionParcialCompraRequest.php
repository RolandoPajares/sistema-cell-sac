<?php

namespace App\Http\Requests\Gestion\Comercial\Compras;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase RegistrarRecepcionParcialCompraRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RegistrarRecepcionParcialCompraRequest extends FormRequest
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
