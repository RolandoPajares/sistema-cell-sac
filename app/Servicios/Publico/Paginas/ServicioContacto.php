<?php

namespace App\Servicios\Publico\Paginas;

use App\Models\Comunicacion\MensajeContacto;

/**
 * Define la responsabilidad de la clase ServicioContacto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioContacto
{
    /** @param array{nombre:string, contacto:string, mensaje:string} $datos */
    public function registrar(array $datos): MensajeContacto
    {
        return MensajeContacto::create($datos + [
            'estado' => 'Nuevo',
            'creado_en' => now(),
        ]);
    }
}
