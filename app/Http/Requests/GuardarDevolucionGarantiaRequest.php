<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarDevolucionGarantiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:40', Rule::unique('devoluciones_garantias', 'codigo')],
            'pedido_id' => ['required', 'integer', 'exists:pedidos,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'tipo' => ['required', Rule::in(['Devolución', 'Garantía', 'Cambio'])],
            'motivo' => ['required', 'string', 'max:5000'],
            'estado' => ['required', Rule::in(['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'])],
            'fecha_solicitud' => ['required', 'date'],
            'fecha_limite' => ['nullable', 'date', 'after_or_equal:fecha_solicitud'],
            'resolucion' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
