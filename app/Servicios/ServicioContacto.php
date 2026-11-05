<?php

namespace App\Servicios;

use App\Models\Comunicacion\MensajeContacto;

class ServicioContacto
{
    /** @param array{nombre:string, contacto:string, mensaje:string} $data */
    public function register(array $data): MensajeContacto
    {
        return MensajeContacto::create($data + [
            'estado' => 'Nuevo',
            'creado_en' => now(),
        ]);
    }
}
