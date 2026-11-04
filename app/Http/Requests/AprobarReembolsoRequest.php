<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AprobarReembolsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return ['confirmacion' => ['required', Rule::in(['aprobar'])]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'confirmacion.required' => 'Confirma la aprobación antes de continuar.',
            'confirmacion.in' => 'La confirmación no es válida.',
        ];
    }
}
