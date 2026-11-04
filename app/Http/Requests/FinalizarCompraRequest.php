<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FinalizarCompraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.pedidos.crear');
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
        ];
    }
}
