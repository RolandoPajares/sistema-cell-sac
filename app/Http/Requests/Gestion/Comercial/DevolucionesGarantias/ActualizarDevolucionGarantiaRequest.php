<?php

namespace App\Http\Requests\Gestion\Comercial\DevolucionesGarantias;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase ActualizarDevolucionGarantiaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarDevolucionGarantiaRequest extends GuardarDevolucionGarantiaRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allows($this->user(), 'gestion.devoluciones.actualizar');
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        $returnRequest = $this->route('devolucionGarantia');

        return array_replace(parent::rules(), [
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => [
                'required',
                'string',
                'max:40',
                Rule::unique('devoluciones_garantias', 'codigo')->ignore($returnRequest),
            ],
        ]);
    }
}
