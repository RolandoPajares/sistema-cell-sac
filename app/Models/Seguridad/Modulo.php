<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\ModuloFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<ModuloFactory> */
    use HasFactory;

    protected $table = 'modulos';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'icono', 'orden', 'habilitado'];

    protected function casts(): array
    {
        return ['orden' => 'integer', 'habilitado' => 'boolean'];
    }

    public function pantallas(): HasMany
    {
        return $this->hasMany(Pantalla::class, 'modulo_id')->orderBy('nombre');
    }
}
