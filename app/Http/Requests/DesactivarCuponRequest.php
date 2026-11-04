<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class DesactivarCuponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        return ['confirmacion' => ['required', 'accepted']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['confirmacion.accepted' => 'Confirma la cancelación del cupón antes de continuar.'];
    }
}
