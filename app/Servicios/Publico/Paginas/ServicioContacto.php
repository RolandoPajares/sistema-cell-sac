<?php

namespace App\Servicios\Publico\Paginas;

use App\Mail\ConfirmacionMensajeContacto;
use App\Models\Comunicacion\MensajeContacto;
use Illuminate\Support\Facades\Mail;
use Throwable;

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
        // Crea el mensaje de contacto con los datos previamente validados.
        $mensaje = MensajeContacto::create($datos + [
            'estado' => 'Nuevo',
            'creado_en' => now(),
        ]);

        if (filter_var($mensaje->contacto, FILTER_VALIDATE_EMAIL) !== false) {
            try {
                Mail::to($mensaje->contacto)->queue(new ConfirmacionMensajeContacto(
                    nombre: $mensaje->nombre,
                    mensaje: $mensaje->mensaje,
                ));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $mensaje;
    }
}
