<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarSegmentoClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    public function rules(): array
    {
        $segmentId = $this->route('segment')?->getKey();

        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('segmentos_clientes', 'nombre')->ignore($segmentId)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', Rule::exists('roles', 'codigo')->where('activo', true)],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
