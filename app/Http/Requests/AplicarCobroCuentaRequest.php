<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AplicarCobroCuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cuenta_cobrar_id' => ['required', 'integer', 'exists:cuentas_por_cobrar,id'],
            'pago_id' => ['required', 'integer', 'exists:pagos,id'],
            'importe' => ['required', 'regex:/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/'],
        ];
    }
}
