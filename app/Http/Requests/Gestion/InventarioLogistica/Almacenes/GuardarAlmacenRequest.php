<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Almacenes;

use App\Models\Organizacion\Almacen;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
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
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => $this->isMethod('post')
                ? ['required', 'integer', Rule::exists('sedes', 'id')]
                : ['prohibited'],
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', $reglaCodigoUnico],
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:140'],
            // Valida el campo 'tipo' con las reglas definidas en la instruccion siguiente.
            'tipo' => ['required', 'string', 'max:30'],
            // Valida el campo 'permite_venta' con las reglas definidas en la instruccion siguiente.
            'permite_venta' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'regex'.
            'codigo.regex' => 'El código solo puede contener letras, números, guion y guion bajo.',
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'unique'.
            'codigo.unique' => 'Ya existe un almacén con ese código en la sede seleccionada.',
            // Muestra el mensaje del campo 'nombre' cuando falla la regla 'max'.
            'nombre.max' => 'El nombre del almacén no puede superar 140 caracteres.',
            // Muestra el mensaje del campo 'sede_id' cuando falla la regla 'exists'.
            'sede_id.exists' => 'Selecciona una sede registrada.',
            // Muestra el mensaje del campo 'tipo' cuando falla la regla 'max'.
            'tipo.max' => 'El tipo no puede superar 30 caracteres.',
        ];
    }
}
