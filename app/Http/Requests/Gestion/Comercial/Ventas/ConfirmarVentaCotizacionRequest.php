<?php

namespace App\Http\Requests\Gestion\Comercial\Ventas;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ConfirmarVentaCotizacionRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ConfirmarVentaCotizacionRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'dispositivos' con las reglas definidas en la instruccion siguiente.
            'dispositivos' => ['sometimes', 'array'],
            // Valida el campo 'dispositivos.*' con las reglas definidas en la instruccion siguiente.
            'dispositivos.*' => ['required', 'array', 'min:1'],
            // Valida el campo 'dispositivos.*.*' con las reglas definidas en la instruccion siguiente.
            'dispositivos.*.*' => ['required', 'integer', 'distinct', 'exists:dispositivos,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'dispositivos' cuando falla la regla '*.required'.
            'dispositivos.*.required' => 'Selecciona los IMEI o números de serie requeridos por cada detalle.',
            // Muestra el mensaje del campo 'dispositivos' cuando falla la regla '*.*.distinct'.
            'dispositivos.*.*.distinct' => 'No puedes asociar el mismo dispositivo más de una vez a la venta.',
            // Muestra el mensaje del campo 'dispositivos' cuando falla la regla '*.*.exists'.
            'dispositivos.*.*.exists' => 'Uno de los IMEI o números de serie ya no existe.',
        ];
    }
}
