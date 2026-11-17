<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase AsignarRolUsuarioRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AsignarRolUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.asignar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'rol_id' con las reglas definidas en la instruccion siguiente.
            'rol_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('activo', true)],
            // Valida el campo 'alcance' con las reglas definidas en la instruccion siguiente.
            'alcance' => ['required', 'in:empresa,sede'],
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['nullable', 'integer', Rule::exists('sedes', 'id')->where('activa', true)],
            // Valida el campo 'vigente_hasta' con las reglas definidas en la instruccion siguiente.
            'vigente_hasta' => ['nullable', 'date', 'after:today'],
        ];
    }
}
