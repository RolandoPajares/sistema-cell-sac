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
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            // Valida el campo 'tipo' con las reglas definidas en la instruccion siguiente.
            'tipo' => ['required', Rule::in(['minorista', 'mayorista'])],
            // Valida el campo 'tipo_documento' con las reglas definidas en la instruccion siguiente.
            'tipo_documento' => ['nullable', Rule::in(['dni', 'ruc'])],
            // Valida el campo 'documento' con las reglas definidas en la instruccion siguiente.
            'documento' => ['required', 'string', 'max:20', Rule::unique('clientes', 'documento')->ignore($customer)],
            // Valida el campo 'razon_social' con las reglas definidas en la instruccion siguiente.
            'razon_social' => [Rule::requiredIf(fn (): bool => $this->input('tipo_documento') === 'ruc'), 'nullable', 'string', 'max:160'],
            // Valida el campo 'nombre_contacto' con las reglas definidas en la instruccion siguiente.
            'nombre_contacto' => ['required', 'string', 'max:160'],
            // Valida el campo 'correo' con las reglas definidas en la instruccion siguiente.
            'correo' => ['required', 'email', 'max:160'],
            // Valida el campo 'telefono' con las reglas definidas en la instruccion siguiente.
            'telefono' => ['nullable', 'string', 'max:30'],
            // Valida el campo 'ciudad' con las reglas definidas en la instruccion siguiente.
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}
