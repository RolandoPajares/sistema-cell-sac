<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $table = 'mensajes_contacto';

    protected $fillable = ['nombre', 'contacto', 'mensaje', 'estado', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }
}
