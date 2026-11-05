<?php

namespace App\Models\Comunicacion;

use Illuminate\Database\Eloquent\Model;

class MensajeContacto extends Model
{
    protected $table = 'mensajes_contacto';

    protected $fillable = ['nombre', 'contacto', 'mensaje', 'estado', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }
}
