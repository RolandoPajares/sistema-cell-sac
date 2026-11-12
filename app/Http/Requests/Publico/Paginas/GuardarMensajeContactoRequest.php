<?php

namespace App\Http\Requests\Publico\Paginas;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Define la responsabilidad de la clase GuardarMensajeContactoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarMensajeContactoRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return true;
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
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120'],
            // Valida el campo 'contacto' con las reglas definidas en la instruccion siguiente.
            'contacto' => ['required', 'string', 'max:160'],
            // Valida el campo 'mensaje' con las reglas definidas en la instruccion siguiente.
            'mensaje' => ['required', 'string', 'max:5000'],
        ];
    }
}
