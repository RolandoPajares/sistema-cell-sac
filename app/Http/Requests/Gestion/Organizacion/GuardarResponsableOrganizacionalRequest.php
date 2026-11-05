<?php

namespace App\Http\Requests\Gestion\Organizacion;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GuardarResponsableOrganizacionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'sede_id' => ['required', 'integer', 'exists:sedes,id'],
            'tipo_recurso' => ['required', Rule::in(['sede', 'almacen'])],
            'almacen_id' => ['nullable', 'required_if:tipo_recurso,almacen', 'integer', 'exists:almacenes,id'],
            'responsable_usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'cargo' => ['required', 'string', 'max:100'],
            'vigente_desde' => ['required', 'date_format:Y-m-d'],
            'vigente_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:vigente_desde'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}
