<?php

namespace App\Models\Comunicacion;

use Illuminate\Database\Eloquent\Model;

/**
 * Define la responsabilidad de la clase MensajeContacto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class MensajeContacto extends Model
{
    protected $table = 'mensajes_contacto';

    protected $fillable = ['nombre', 'contacto', 'mensaje', 'estado', 'creado_en'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase MensajeContacto.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }
}
