<?php

namespace App\Http\Requests;

use App\Models\Seguridad\Rol;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.actualizar');
    }

    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof Rol ? $role->getKey() : $role;

        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('roles', 'nombre')->ignore($roleId)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'alcance_permitido' => ['required', Rule::in(['empresa', 'sede', 'propio'])],
        ];
    }
}
