<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\PantallaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pantalla extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<PantallaFactory> */
    use HasFactory;

    protected $table = 'pantallas';

    protected $fillable = ['modulo_id', 'codigo', 'nombre', 'ruta', 'icono', 'habilitada'];

    protected function casts(): array
    {
        return ['habilitada' => 'boolean'];
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }
}
