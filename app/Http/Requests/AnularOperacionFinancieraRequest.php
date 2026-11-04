<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class AnularOperacionFinancieraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User
            && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['motivo' => ['required', 'string', 'min:10', 'max:500']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'motivo.required' => 'Explica el motivo de la anulación.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede superar 500 caracteres.',
        ];
    }
}
