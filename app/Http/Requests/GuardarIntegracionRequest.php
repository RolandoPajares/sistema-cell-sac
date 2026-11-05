<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

final class GuardarIntegracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.integraciones.configurar');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:80', 'regex:/\A[A-Za-z0-9._-]+\z/'],
            'nombre' => ['required', 'string', 'max:140'],
            'proveedor' => ['required', 'string', 'max:100'],
        ];
    }
}
