<?php

namespace App\Http\Requests\Gestion\Configuracion;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GuardarConfiguracionSistemaRequest extends FormRequest
{
    // Decide si la persona que envia la solicitud tiene permiso para ejecutar la operacion.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.configuracion.actualizar');
    }

    // Define las reglas que deben cumplir los datos recibidos antes de continuar.
    public function rules(): array
    {
        return [
            // Valida el campo 'grupo' con las reglas definidas en la instruccion siguiente.
            'grupo' => ['required', Rule::in(['general', 'parametros-comerciales', 'inventario', 'finanzas'])],
            // Valida el campo 'alcance' con las reglas definidas en la instruccion siguiente.
            'alcance' => ['required', Rule::in(['global', 'sede'])],
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
            // Valida el campo 'configuracion' con las reglas definidas en la instruccion siguiente.
            'configuracion' => ['required', 'array'],
            // Valida el campo 'configuracion.nombre_sistema' con las reglas definidas en la instruccion siguiente.
            'configuracion.nombre_sistema' => ['sometimes', 'required', 'string', 'max:180'],
            // Valida el campo 'configuracion.nombre_corto' con las reglas definidas en la instruccion siguiente.
            'configuracion.nombre_corto' => ['nullable', 'string', 'max:80'],
            // Valida el campo 'configuracion.idioma' con las reglas definidas en la instruccion siguiente.
            'configuracion.idioma' => ['nullable', 'string', 'max:20'],
            // Valida el campo 'configuracion.zona_horaria' con las reglas definidas en la instruccion siguiente.
            'configuracion.zona_horaria' => ['nullable', 'timezone'],
            // Valida el campo 'configuracion.formato_fecha' con las reglas definidas en la instruccion siguiente.
            'configuracion.formato_fecha' => ['nullable', 'string', 'max:40'],
            // Valida el campo 'configuracion.formato_hora' con las reglas definidas en la instruccion siguiente.
            'configuracion.formato_hora' => ['nullable', 'string', 'max:40'],
            // Valida el campo 'configuracion.separador_decimal' con las reglas definidas en la instruccion siguiente.
            'configuracion.separador_decimal' => ['nullable', Rule::in(['.', ','])],
            // Valida el campo 'configuracion.registros_por_pagina' con las reglas definidas en la instruccion siguiente.
            'configuracion.registros_por_pagina' => ['nullable', 'integer', 'min:5', 'max:200'],
            // Valida el campo 'configuracion.moneda' con las reglas definidas en la instruccion siguiente.
            'configuracion.moneda' => ['nullable', 'string', 'size:3'],
            // Valida el campo 'configuracion.vigencia_cotizacion' con las reglas definidas en la instruccion siguiente.
            'configuracion.vigencia_cotizacion' => ['nullable', 'integer', 'min:1', 'max:365'],
            // Valida el campo 'configuracion.descuento_maximo' con las reglas definidas en la instruccion siguiente.
            'configuracion.descuento_maximo' => ['nullable', 'numeric', 'min:0', 'max:100'],
            // Valida el campo 'configuracion.plazo_pedido' con las reglas definidas en la instruccion siguiente.
            'configuracion.plazo_pedido' => ['nullable', 'integer', 'min:0', 'max:365'],
            // Valida el campo 'configuracion.stock_minimo_alerta' con las reglas definidas en la instruccion siguiente.
            'configuracion.stock_minimo_alerta' => ['nullable', 'integer', 'min:0'],
            // Valida el campo 'configuracion.permitir_stock_negativo' con las reglas definidas en la instruccion siguiente.
            'configuracion.permitir_stock_negativo' => ['nullable', 'boolean'],
            // Valida el campo 'configuracion.requiere_aprobacion_ajuste' con las reglas definidas en la instruccion siguiente.
            'configuracion.requiere_aprobacion_ajuste' => ['nullable', 'boolean'],
            // Valida el campo 'configuracion.redondeo_monetario' con las reglas definidas en la instruccion siguiente.
            'configuracion.redondeo_monetario' => ['nullable', 'integer', 'min:0', 'max:4'],
            // Valida el campo 'configuracion.tolerancia_arqueo' con las reglas definidas en la instruccion siguiente.
            'configuracion.tolerancia_arqueo' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            // Valida el campo 'configuracion.tolerancia_pago' con las reglas definidas en la instruccion siguiente.
            'configuracion.tolerancia_pago' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    // Ajusta los datos recibidos antes de que Laravel aplique las reglas de validacion.
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sede_id' => $this->input('alcance') === 'sede' ? $this->input('sede_id') : null,
        ]);
    }
}
