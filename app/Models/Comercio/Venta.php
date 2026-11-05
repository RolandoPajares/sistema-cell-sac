<?php

namespace App\Models\Comercio;

use App\Models\Clientes\Cliente;
use App\Models\Finanzas\Pago;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = ['empresa_id', 'sede_id', 'almacen_id', 'pedido_id', 'cliente_id', 'usuario_id', 'codigo', 'estado', 'moneda', 'subtotal', 'descuento', 'impuestos', 'total', 'fecha_venta', 'canal', 'observaciones'];

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected function casts(): array
    {
        return ['fecha_venta' => 'datetime', 'creado_en' => 'datetime', 'total' => 'decimal:2'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }
}
