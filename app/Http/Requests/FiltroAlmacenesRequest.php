<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase FiltroAlmacenesRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FiltroAlmacenesRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        $usuario = $this->user();

        return $usuario instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this)
            && app(ServicioAutorizacion::class)->allows($usuario, 'gestion.organizacion.consultar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:120'],
            'sede_id' => ['nullable', 'integer', Rule::exists('sedes', 'id')],
            'tipo' => ['nullable', 'string', 'max:30'],
            'estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
        ];
    }
}
