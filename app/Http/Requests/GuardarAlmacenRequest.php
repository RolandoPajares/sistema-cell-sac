<?php

namespace App\Http\Requests;

use App\Models\Organizacion\Almacen;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarAlmacenRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarAlmacenRequest extends FormRequest
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
            && app(ServicioAutorizacion::class)->allows($usuario, 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        $almacen = $this->route('almacen');
        $sedeId = $this->input('sede_id', $almacen instanceof Almacen ? $almacen->sede_id : null);
        $reglaCodigoUnico = Rule::unique('almacenes', 'codigo')
            ->where('sede_id', $sedeId)
            ->ignore($almacen instanceof Almacen ? $almacen->getKey() : null);

        return [
            'sede_id' => $this->isMethod('post')
                ? ['required', 'integer', Rule::exists('sedes', 'id')]
                : ['prohibited'],
            'codigo' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', $reglaCodigoUnico],
            'nombre' => ['required', 'string', 'max:140'],
            'tipo' => ['required', 'string', 'max:30'],
            'permite_venta' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo puede contener letras, números, guion y guion bajo.',
            'codigo.unique' => 'Ya existe un almacén con ese código en la sede seleccionada.',
            'nombre.max' => 'El nombre del almacén no puede superar 140 caracteres.',
            'sede_id.exists' => 'Selecciona una sede registrada.',
            'tipo.max' => 'El tipo no puede superar 30 caracteres.',
        ];
    }
}
