<?php

namespace App\Http\Requests;

class ActualizarCampaniaRequest extends GuardarCampaniaRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
