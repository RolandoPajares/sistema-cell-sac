<?php

namespace App\Models\Inventario;

use App\Models\Catalogo\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = ['producto_id', 'usuario_id', 'tipo_movimiento', 'cantidad', 'notas', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
