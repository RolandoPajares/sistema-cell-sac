<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Models\Seguridad\Rol;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarRolRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarRolRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.actualizar');
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
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
