<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Productos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarPrecioMayoristaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarPrecioMayoristaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        abort_unless(Schema::hasTable('precios_mayoristas'), 503,
            'Los precios mayoristas requieren aplicar primero la estructura SQL aprobada.');
        $empresaId = app(ServicioAutorizacion::class)->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para registrar precios mayoristas.');

        return [
            // Valida el campo 'producto_id' con las reglas definidas en la instruccion siguiente.
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->where('empresa_id', $empresaId)],
            // Valida el campo 'precio' con las reglas definidas en la instruccion siguiente.
            'precio' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            // Valida el campo 'vigente_desde' con las reglas definidas en la instruccion siguiente.
            'vigente_desde' => ['required', 'date'],
            // Valida el campo 'vigente_hasta' con las reglas definidas en la instruccion siguiente.
            'vigente_hasta' => ['nullable', 'date', 'after_or_equal:vigente_desde'],
            // Valida el campo 'motivo' con las reglas definidas en la instruccion siguiente.
            'motivo' => ['required', 'string', 'max:300'],
        ];
    }
}
