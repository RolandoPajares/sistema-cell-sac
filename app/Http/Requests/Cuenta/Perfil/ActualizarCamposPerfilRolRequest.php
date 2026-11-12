<?php

namespace App\Http\Requests\Cuenta\Perfil;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarCamposPerfilRolRequest extends FormRequest
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
            // Valida el campo 'campos' con las reglas definidas en la instruccion siguiente.
            'campos' => ['required', 'array'],
            // Valida el campo 'campos.*.id' con las reglas definidas en la instruccion siguiente.
            'campos.*.id' => ['required', 'integer', Rule::exists('perfil_campos', 'id')],
            // Valida el campo 'campos.*.visible' con las reglas definidas en la instruccion siguiente.
            'campos.*.visible' => ['sometimes', 'boolean'],
            // Valida el campo 'campos.*.editable' con las reglas definidas en la instruccion siguiente.
            'campos.*.editable' => ['sometimes', 'boolean'],
            // Valida el campo 'campos.*.requerido' con las reglas definidas en la instruccion siguiente.
            'campos.*.requerido' => ['sometimes', 'boolean'],
        ];
    }
}
