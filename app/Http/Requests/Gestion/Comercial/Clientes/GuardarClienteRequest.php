<?php

namespace App\Http\Requests\Gestion\Comercial\Clientes;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarClienteRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarClienteRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'tipo' con las reglas definidas en la instruccion siguiente.
            'tipo' => ['required', Rule::in(['minorista', 'mayorista'])],
            // Valida el campo 'tipo_documento' con las reglas definidas en la instruccion siguiente.
            'tipo_documento' => ['nullable', Rule::in(['dni', 'ruc'])],
            // Valida el campo 'documento' con las reglas definidas en la instruccion siguiente.
            'documento' => ['required', 'string', 'max:20', Rule::unique('clientes', 'documento')],
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

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'tipo_documento' => 'tipo de documento',
            'documento' => 'documento de identificación',
            'nombre_contacto' => 'nombre de contacto',
            'razon_social' => 'razón social',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'tipo_documento' cuando falla la regla 'in'.
            'tipo_documento.in' => 'El tipo de documento seleccionado no es válido.',
            // Muestra el mensaje del campo 'documento' cuando falla la regla 'unique'.
            'documento.unique' => 'El documento ya está registrado.',
        ];
    }
}
