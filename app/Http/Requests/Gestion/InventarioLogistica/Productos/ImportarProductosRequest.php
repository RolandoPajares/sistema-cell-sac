<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Productos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

class ImportarProductosRequest extends FormRequest
{
    // Decide si la persona que envia la solicitud tiene permiso para ejecutar la operacion.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    // Define las reglas que deben cumplir los datos recibidos antes de continuar.
    public function rules(): array
    {
        return [
            // Valida el campo 'archivo' con las reglas definidas en la instruccion siguiente.
            'archivo' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:20480'],
        ];
    }
}
