<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarRolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:40', 'regex:/^[a-z][a-z0-9_]*$/', 'not_in:superadministrador', Rule::unique('roles', 'codigo')],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'alcance_permitido' => ['required', 'in:empresa,sede,propio'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.not_in' => 'El código superadministrador está reservado y no se puede crear.',
        ];
    }
}
