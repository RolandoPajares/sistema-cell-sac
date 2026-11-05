<?php

namespace App\Http\Requests;

class ActualizarProductoRequest extends GuardarProductoRequest
{
    public function rules(): array
    {
        $reglas = parent::rules();
        unset($reglas['existencias']);

        return $reglas;
    }
}
