<?php

namespace App\Models\Finanzas;

use App\Models\Organizacion\Sede;
use Database\Factories\Finanzas\CajaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    /** @use HasFactory<CajaFactory> */
    use HasFactory;

    protected $table = 'cajas';

    protected $fillable = ['sede_id', 'codigo', 'nombre', 'activa', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['activa' => 'boolean', 'creado_en' => 'datetime'];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionCaja::class, 'caja_id')->latest('id');
    }
}
