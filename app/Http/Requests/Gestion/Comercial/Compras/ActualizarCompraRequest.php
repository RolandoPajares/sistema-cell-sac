<?php

namespace App\Http\Requests\Gestion\Comercial\Compras;

/**
 * Valida los datos permitidos para actualizar una compra pendiente.
 */
class ActualizarCompraRequest extends GuardarCompraRequest
{
    /**
     * Define la validación de edición sin reutilizar el token de creación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['compra_token']);

        return $rules;
    }
}
