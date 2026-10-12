<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends StoreSupplierRequest
{
    public function rules(): array
    {
        $supplier = $this->route('supplier');

        return [
            'nombre' => ['required', 'string', 'max:160'],
            'ruc' => ['required', 'string', 'max:20', Rule::unique('proveedores', 'ruc')->ignore($supplier)],
            'correo' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}
