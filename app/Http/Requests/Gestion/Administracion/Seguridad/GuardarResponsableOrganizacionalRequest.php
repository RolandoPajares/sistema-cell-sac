<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarResponsableOrganizacionalRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class GuardarResponsableOrganizacionalRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['required', 'integer', 'exists:sedes,id'],
            // Valida el campo 'tipo_recurso' con las reglas definidas en la instruccion siguiente.
            'tipo_recurso' => ['required', Rule::in(['sede', 'almacen'])],
            // Valida el campo 'almacen_id' con las reglas definidas en la instruccion siguiente.
            'almacen_id' => ['nullable', 'required_if:tipo_recurso,almacen', 'integer', 'exists:almacenes,id'],
            // Valida el campo 'responsable_usuario_id' con las reglas definidas en la instruccion siguiente.
            'responsable_usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            // Valida el campo 'cargo' con las reglas definidas en la instruccion siguiente.
            'cargo' => ['required', 'string', 'max:100'],
            // Valida el campo 'vigente_desde' con las reglas definidas en la instruccion siguiente.
            'vigente_desde' => ['required', 'date_format:Y-m-d'],
            // Valida el campo 'vigente_hasta' con las reglas definidas en la instruccion siguiente.
            'vigente_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:vigente_desde'],
            // Valida el campo 'motivo' con las reglas definidas en la instruccion siguiente.
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}
