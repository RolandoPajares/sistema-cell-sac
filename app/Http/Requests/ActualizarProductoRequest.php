<?php

namespace App\Http\Requests;

class ActualizarProductoRequest extends GuardarProductoRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
