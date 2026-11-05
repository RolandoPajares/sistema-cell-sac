<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class GuardarCuponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        $unicidadCodigo = Rule::unique('cupones', 'codigo');
        if (Schema::hasColumn('cupones', 'empresa_id')) {
            $unicidadCodigo->where('empresa_id', app(ServicioAutorizacion::class)->activeCompanyId());
        }

        return [
            'codigo' => ['required', 'alpha_dash', 'max:50', $unicidadCodigo],
            'tipo_descuento' => ['required', Rule::in(['porcentaje', 'fijo'])],
            'valor' => ['required', 'numeric', 'gt:0', 'max:99999999.99', Rule::when($this->input('tipo_descuento') === 'porcentaje', ['lte:100'])],
            'minimo_compra' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'limite_usos' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'inicia_en' => ['required', 'date'],
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
            'codigo.unique' => 'Ese código de cupón ya está registrado.',
            'tipo_descuento.in' => 'Selecciona un tipo de descuento válido.',
            'valor.lte' => 'El descuento porcentual no puede superar el 100 %.',
            'valor.max' => 'El valor supera el máximo permitido.',
            'minimo_compra.max' => 'La compra mínima supera el máximo permitido.',
            'limite_usos.max' => 'El límite de usos supera el máximo permitido.',
            'finaliza_en.after' => 'La fecha final debe ser posterior a la fecha inicial.',
        ];
    }
}
