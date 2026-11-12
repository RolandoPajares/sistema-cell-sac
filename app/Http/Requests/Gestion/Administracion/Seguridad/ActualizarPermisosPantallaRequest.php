<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarPermisosPantallaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.actualizar');
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
            // Valida el campo 'modulo_id' con las reglas definidas en la instruccion siguiente.
            'modulo_id' => ['required', 'integer', Rule::exists('modulos', 'id')->where('habilitado', true)],
            // Valida el campo 'pantalla_id' con las reglas definidas en la instruccion siguiente.
            'pantalla_id' => ['required', 'integer', Rule::exists('pantallas', 'id')->where('habilitada', true)],
            // Valida el campo 'permisos' con las reglas definidas en la instruccion siguiente.
            'permisos' => ['sometimes', 'array'],
            // Valida el campo 'permisos.*' con las reglas definidas en la instruccion siguiente.
            'permisos.*' => ['integer', Rule::exists('permisos', 'id')->where('activo', true)],
            // Valida el campo 'alcance' con las reglas definidas en la instruccion siguiente.
            'alcance' => ['required', 'in:empresa,sede,propio'],
        ];
    }
}
