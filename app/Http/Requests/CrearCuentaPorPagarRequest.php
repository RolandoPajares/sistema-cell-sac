<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class CrearCuentaPorPagarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'compra_id' => ['required', 'integer', 'exists:compras,id'],
            'vence_en' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }
}
