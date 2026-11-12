<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarRolRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarRolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.crear');
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
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => ['required', 'string', 'max:40', 'regex:/^[a-z][a-z0-9_]*$/', 'not_in:superadministrador', Rule::unique('roles', 'codigo')],
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120', Rule::unique('roles', 'nombre')],
            // Valida el campo 'descripcion' con las reglas definidas en la instruccion siguiente.
            'descripcion' => ['nullable', 'string', 'max:255'],
            // Valida el campo 'alcance_permitido' con las reglas definidas en la instruccion siguiente.
            'alcance_permitido' => ['required', 'in:empresa,sede,propio'],
        ];
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define los mensajes que se mostraran cuando falle una regla de validacion.
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'not_in'.
            'codigo.not_in' => 'El código superadministrador está reservado y no se puede crear.',
        ];
    }
}
