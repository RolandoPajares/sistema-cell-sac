<?php

namespace App\Http\Requests\Gestion\Comercial\Clientes;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase ImportarClientesRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ImportarClientesRequest extends FormRequest
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
            // Valida el campo 'archivo' con las reglas definidas en la instruccion siguiente.
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            // Valida el campo 'confirmado' con las reglas definidas en la instruccion siguiente.
            'confirmado' => ['accepted'],
        ];
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define los mensajes que se mostraran cuando falle una regla de validacion.
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'archivo' cuando falla la regla 'required'.
            'archivo.required' => 'Selecciona un archivo CSV.',
            // Muestra el mensaje del campo 'archivo' cuando falla la regla 'mimes'.
            'archivo.mimes' => 'La importación acepta únicamente archivos CSV UTF-8.',
            // Muestra el mensaje del campo 'confirmado' cuando falla la regla 'accepted'.
            'confirmado.accepted' => 'Confirma que revisaste el archivo antes de importarlo.',
        ];
    }
}
