<?php

namespace App\Http\Requests\Gestion\Finanzas\Cajas;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase FiltroCajasRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FiltroCajasRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->allowsGlobal($this->user(), 'gestion.seguridad.consultar')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'buscar' con las reglas definidas en la instruccion siguiente.
            'buscar' => ['nullable', 'string', 'max:120'],
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
            // Valida el campo 'activa' con las reglas definidas en la instruccion siguiente.
            'activa' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'buscar' cuando falla la regla 'max'.
            'buscar.max' => 'La búsqueda no puede superar 120 caracteres.',
            // Muestra el mensaje del campo 'sede_id' cuando falla la regla 'exists'.
            'sede_id.exists' => 'La sede seleccionada no existe.',
            // Muestra el mensaje del campo 'activa' cuando falla la regla 'boolean'.
            'activa.boolean' => 'Selecciona un estado válido.',
        ];
    }
}
