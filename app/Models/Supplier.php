<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
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
        return $this->belongsToMany(Product::class, 'producto_proveedor', 'proveedor_id', 'producto_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'proveedor_id');
    }
}
