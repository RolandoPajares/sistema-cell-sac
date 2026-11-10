<?php

namespace App\Http\Requests\Gestion\Comercial\Clientes;

use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarClienteRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarClienteRequest extends GuardarClienteRequest
{
    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'tipo' => ['required', Rule::in(['minorista', 'mayorista'])],
            'tipo_documento' => ['nullable', Rule::in(['dni', 'ruc'])],
            'documento' => ['required', 'string', 'max:20', Rule::unique('clientes', 'documento')->ignore($customer)],
            'razon_social' => [Rule::requiredIf(fn (): bool => $this->input('tipo_documento') === 'ruc'), 'nullable', 'string', 'max:160'],
            'nombre_contacto' => ['required', 'string', 'max:160'],
            'correo' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}
