<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsignarRolUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
    public function rules(): array
    {
        return [
            'rol_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('activo', true)],
            'alcance' => ['required', 'in:empresa,sede'],
            'sede_id' => ['nullable', 'integer', Rule::exists('sedes', 'id')->where('activa', true)],
            'vigente_hasta' => ['nullable', 'date', 'after:today'],
        ];
    }
}
