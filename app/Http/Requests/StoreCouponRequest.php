<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'marketing'], true);
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'alpha_dash', 'max:50', Rule::unique('cupones', 'codigo')],
            'tipo_descuento' => ['required', Rule::in(['porcentaje', 'fijo'])],
            'valor' => ['required', 'numeric', 'gt:0'],
            'minimo_compra' => ['required', 'numeric', 'min:0'],
            'limite_usos' => ['nullable', 'integer', 'min:1'],
            'inicia_en' => ['required', 'date'],
            'finaliza_en' => ['required', 'date', 'after:inicia_en'],
        ];
    }
}
