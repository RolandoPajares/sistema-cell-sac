<?php

namespace App\Http\Requests\Gestion\Comercial\Proveedores;

use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarProveedorRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarProveedorRequest extends GuardarProveedorRequest
{
    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        $supplier = $this->route('supplier');

        return [
            'nombre' => ['required', 'string', 'max:160'],
            'tipo_persona' => ['required', Rule::in(['natural', 'juridica'])],
            'tipo_documento' => ['required', Rule::in(['dni', 'ruc'])],
            'ruc' => [
                'required', 'string', 'max:20',
                $this->input('tipo_documento') === 'dni' ? 'size:8' : 'size:11',
                'regex:/^[0-9]+$/', Rule::unique('proveedores', 'ruc')->ignore($supplier),
            ],
            'correo' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}
