<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class FiltroReembolsosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'buscar' => 'búsqueda',
            'estado' => 'estado',
            'desde' => 'fecha inicial',
            'hasta' => 'fecha final',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            'estado.max' => 'El estado no puede superar 30 caracteres.',
            'desde.date_format' => 'La fecha inicial debe tener el formato AAAA-MM-DD.',
            'hasta.date_format' => 'La fecha final debe tener el formato AAAA-MM-DD.',
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
