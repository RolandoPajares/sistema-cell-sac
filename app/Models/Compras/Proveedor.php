<?php

namespace App\Models\Compras;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'ruc', 'correo', 'telefono', 'ciudad', 'activo', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_proveedor', 'proveedor_id', 'producto_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }
}
