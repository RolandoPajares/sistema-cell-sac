<?php

namespace App\Http\Requests\Gestion\Configuracion;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GuardarConfiguracionSistemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.configuracion.actualizar');
    }

    public function rules(): array
    {
        return [
            'grupo' => ['required', Rule::in(['general', 'parametros-comerciales', 'inventario', 'finanzas'])],
            'alcance' => ['required', Rule::in(['global', 'sede'])],
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
            'configuracion' => ['required', 'array'],
            'configuracion.nombre_sistema' => ['sometimes', 'required', 'string', 'max:180'],
            'configuracion.nombre_corto' => ['nullable', 'string', 'max:80'],
            'configuracion.idioma' => ['nullable', 'string', 'max:20'],
            'configuracion.zona_horaria' => ['nullable', 'timezone'],
            'configuracion.formato_fecha' => ['nullable', 'string', 'max:40'],
            'configuracion.formato_hora' => ['nullable', 'string', 'max:40'],
            'configuracion.separador_decimal' => ['nullable', Rule::in(['.', ','])],
            'configuracion.registros_por_pagina' => ['nullable', 'integer', 'min:5', 'max:200'],
            'configuracion.moneda' => ['nullable', 'string', 'size:3'],
            'configuracion.vigencia_cotizacion' => ['nullable', 'integer', 'min:1', 'max:365'],
            'configuracion.descuento_maximo' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'configuracion.plazo_pedido' => ['nullable', 'integer', 'min:0', 'max:365'],
            'configuracion.stock_minimo_alerta' => ['nullable', 'integer', 'min:0'],
            'configuracion.permitir_stock_negativo' => ['nullable', 'boolean'],
            'configuracion.requiere_aprobacion_ajuste' => ['nullable', 'boolean'],
            'configuracion.redondeo_monetario' => ['nullable', 'integer', 'min:0', 'max:4'],
            'configuracion.tolerancia_arqueo' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'configuracion.tolerancia_pago' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sede_id' => $this->input('alcance') === 'sede' ? $this->input('sede_id') : null,
        ]);
    }
}
