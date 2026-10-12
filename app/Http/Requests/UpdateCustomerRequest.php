<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends StoreCustomerRequest
{
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'tipo' => ['required', Rule::in(['minorista', 'mayorista'])],
            'documento' => ['required', 'string', 'max:20', Rule::unique('clientes', 'documento')->ignore($customer)],
            'razon_social' => ['nullable', 'string', 'max:160'],
            'nombre_contacto' => ['required', 'string', 'max:160'],
            'correo' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}
