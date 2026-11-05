<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MovimientoDispositivo extends Model
{
    protected $table = 'movimientos_dispositivos';

    protected $fillable = [
        'dispositivo_id', 'movimiento_inventario_id', 'estado_anterior', 'estado_nuevo', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class, 'dispositivo_id');
    }
}
