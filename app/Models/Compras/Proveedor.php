<?php

namespace App\Models\Compras;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Proveedor dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'ruc', 'correo', 'telefono', 'ciudad', 'activo', 'creado_en', 'empresa_id'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Proveedor.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación products definida por la clase Proveedor.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'productos_proveedores', 'proveedor_id', 'producto_id');
    }

    /**
     * Ejecuta la operación purchases definida por la clase Proveedor.
     *
     * @return HasMany Resultado de la operación.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }
}
