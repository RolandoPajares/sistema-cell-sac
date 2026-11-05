<?php

namespace App\Models\Organizacion;

use Database\Factories\Organizacion\AlmacenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Almacen extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<AlmacenFactory> */
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = ['sede_id', 'codigo', 'nombre', 'tipo', 'permite_venta', 'activo'];

    protected function casts(): array
    {
        return ['permite_venta' => 'boolean', 'activo' => 'boolean'];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
