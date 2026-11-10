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
            'modulo_id' => ['required', 'integer', Rule::exists('modulos', 'id')->where('habilitado', true)],
            'pantalla_id' => ['required', 'integer', Rule::exists('pantallas', 'id')->where('habilitada', true)],
            'permisos' => ['sometimes', 'array'],
            'permisos.*' => ['integer', Rule::exists('permisos', 'id')->where('activo', true)],
            'alcance' => ['required', 'in:empresa,sede,propio'],
        ];
    }
}
