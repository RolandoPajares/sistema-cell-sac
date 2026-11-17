<?php

namespace App\Models\Compras;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Compra dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Compra extends Model
{
    // Usa la tabla 'compras' como origen de los registros de este modelo.
    protected $table = 'compras';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['proveedor_id', 'creado_por_usuario_id', 'total', 'estado', 'fecha_compra', 'activo', 'creado_en', 'actualizado_en', 'empresa_id', 'sede_id', 'almacen_id', 'numero_referencia', 'moneda'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Compra.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'fecha_compra' => 'date', 'activo' => 'boolean', 'creado_en' => 'datetime', 'actualizado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación supplier definida por la clase Compra.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Ejecuta la operación creator definida por la clase Compra.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return HasMany Resultado de la operación.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }
}
