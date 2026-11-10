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
    public function rules(): array
    {
        return [
            'campos' => ['required', 'array'],
            'campos.*.id' => ['required', 'integer', Rule::exists('perfil_campos', 'id')],
            'campos.*.visible' => ['sometimes', 'boolean'],
            'campos.*.editable' => ['sometimes', 'boolean'],
            'campos.*.requerido' => ['sometimes', 'boolean'],
        ];
    }
}
