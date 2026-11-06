<?php

namespace App\Servicios;

use App\Models\Comunicacion\MensajeContacto;

/**
 * Define la responsabilidad de la clase ServicioContacto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
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
