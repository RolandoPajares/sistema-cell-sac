<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltroSesionesCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->hasRole($this->user(), 'administrador')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:120'],
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
            'estado' => ['nullable', Rule::in(['abierta', 'cerrada'])],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            'sede_id.exists' => 'La sede seleccionada no existe.',
            'estado.in' => 'Selecciona un estado válido.',
            'desde.date_format' => 'La fecha inicial debe tener formato AAAA-MM-DD.',
            'hasta.date_format' => 'La fecha final debe tener formato AAAA-MM-DD.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }
}
