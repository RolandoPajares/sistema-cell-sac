<?php

namespace App\Http\Requests;

/**
 * Define la responsabilidad de la clase ActualizarCampaniaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ActualizarCampaniaRequest extends GuardarCampaniaRequest
{
    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return parent::rules();
    }
}
