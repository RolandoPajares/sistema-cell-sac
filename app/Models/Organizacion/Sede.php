<?php

namespace App\Models\Organizacion;

use Database\Factories\Organizacion\SedeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<SedeFactory> */
    use HasFactory;

    protected $table = 'sedes';

    protected $fillable = ['empresa_id', 'codigo', 'nombre', 'direccion', 'telefono', 'zona_horaria', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class, 'sede_id')->orderBy('nombre');
    }
}
