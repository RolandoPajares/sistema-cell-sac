<?php

namespace App\Http\Requests\Gestion\EcommerceMarketing\PromocionesCupones;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarCuponRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarCuponRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        $unicidadCodigo = Rule::unique('cupones', 'codigo');
        if (Schema::hasColumn('cupones', 'empresa_id')) {
            $unicidadCodigo->where('empresa_id', app(ServicioAutorizacion::class)->activeCompanyId());
        }

        return [
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => ['required', 'alpha_dash', 'max:50', $unicidadCodigo],
            // Valida el campo 'tipo_descuento' con las reglas definidas en la instruccion siguiente.
            'tipo_descuento' => ['required', Rule::in(['porcentaje', 'fijo'])],
            // Valida el campo 'valor' con las reglas definidas en la instruccion siguiente.
            'valor' => ['required', 'numeric', 'gt:0', 'max:99999999.99', Rule::when($this->input('tipo_descuento') === 'porcentaje', ['lte:100'])],
            // Valida el campo 'minimo_compra' con las reglas definidas en la instruccion siguiente.
            'minimo_compra' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            // Valida el campo 'limite_usos' con las reglas definidas en la instruccion siguiente.
            'limite_usos' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            // Valida el campo 'inicia_en' con las reglas definidas en la instruccion siguiente.
            'inicia_en' => ['required', 'date'],
            // Valida el campo 'finaliza_en' con las reglas definidas en la instruccion siguiente.
            'finaliza_en' => ['required', 'date', 'after:inicia_en'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'tipo_descuento' => 'tipo de descuento',
            'valor' => 'valor',
            'minimo_compra' => 'compra mínima',
            'limite_usos' => 'límite de usos',
            'inicia_en' => 'fecha inicial',
            'finaliza_en' => 'fecha final',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'unique'.
            'codigo.unique' => 'Ese código de cupón ya está registrado.',
            // Muestra el mensaje del campo 'tipo_descuento' cuando falla la regla 'in'.
            'tipo_descuento.in' => 'Selecciona un tipo de descuento válido.',
            // Muestra el mensaje del campo 'valor' cuando falla la regla 'lte'.
            'valor.lte' => 'El descuento porcentual no puede superar el 100 %.',
            // Muestra el mensaje del campo 'valor' cuando falla la regla 'max'.
            'valor.max' => 'El valor supera el máximo permitido.',
            // Muestra el mensaje del campo 'minimo_compra' cuando falla la regla 'max'.
            'minimo_compra.max' => 'La compra mínima supera el máximo permitido.',
            // Muestra el mensaje del campo 'limite_usos' cuando falla la regla 'max'.
            'limite_usos.max' => 'El límite de usos supera el máximo permitido.',
            // Muestra el mensaje del campo 'finaliza_en' cuando falla la regla 'after'.
            'finaliza_en.after' => 'La fecha final debe ser posterior a la fecha inicial.',
        ];
    }
}
