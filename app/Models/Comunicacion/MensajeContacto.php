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
    // Usa la tabla 'mensajes_contacto' como origen de los registros de este modelo.
    protected $table = 'mensajes_contacto';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
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
