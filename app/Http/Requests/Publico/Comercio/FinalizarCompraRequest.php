<?php

namespace App\Http\Requests\Publico\Comercio;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase FinalizarCompraRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FinalizarCompraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user() === null) {
            return $this->session()->has('cart');
        }

        return ($this->user()->can('gestion.cotizaciones.consultar') || $this->user()->can('gestion.pedidos.consultar'))
            || app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.pedidos.crear');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'checkout_token' => [
                'required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $sessionToken = $this->session()->get('checkout_token');
                    if (! is_string($value) || ! is_string($sessionToken) || ! hash_equals($sessionToken, $value)) {
                        $fail('La solicitud de compra ha vencido; vuelve a revisar el carrito.');
                    }
                },
            ],
            'nombre' => ['required_if:user_id,guest', 'nullable', 'string', 'max:160'],
            'correo' => ['required_if:user_id,guest', 'nullable', 'email:rfc', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['required_if:user_id,guest', 'nullable', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:500'],
            'medio_pago_id' => ['nullable', 'integer', 'exists:medios_pago,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user() === null) {
            $this->merge(['user_id' => 'guest']);
        }
    }
}
