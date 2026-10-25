<?php

namespace App\Models\Compras;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = ['proveedor_id', 'creado_por', 'total', 'estado', 'fecha_compra', 'activo', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'fecha_compra' => 'date', 'activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }
}
