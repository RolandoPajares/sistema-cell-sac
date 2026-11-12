<?php

namespace App\Http\Requests\Gestion\EcommerceMarketing\Segmentos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarSegmentoClienteRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarSegmentoClienteRequest extends FormRequest
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
        $segmentId = $this->route('segment')?->getKey();

        return [
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120', Rule::unique('segmentos_clientes', 'nombre')->ignore($segmentId)],
            // Valida el campo 'roles' con las reglas definidas en la instruccion siguiente.
            'roles' => ['required', 'array', 'min:1'],
            // Valida el campo 'roles.*' con las reglas definidas en la instruccion siguiente.
            'roles.*' => ['required', Rule::exists('roles', 'codigo')->where('activo', true)],
            // Valida el campo 'activo' con las reglas definidas en la instruccion siguiente.
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
