<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertirCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return [
            'almacen_id' => [
                'required',
                'integer',
                Rule::exists('almacenes', 'id')->where(fn ($consulta) => $consulta->where('activo', true)->where('permite_venta', true)),
            ],
        ];
    }
}
