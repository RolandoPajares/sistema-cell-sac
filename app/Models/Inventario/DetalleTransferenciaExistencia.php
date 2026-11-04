<?php

namespace App\Models\Inventario;

use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use Database\Factories\Inventario\DetalleTransferenciaExistenciaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleTransferenciaExistencia extends Model
{
    /** @use HasFactory<DetalleTransferenciaExistenciaFactory> */
    use HasFactory;

    protected $table = 'detalles_transferencias_existencias';

    protected $fillable = [
        'transferencia_id', 'producto_id', 'variante_id', 'cantidad_solicitada', 'cantidad_enviada',
        'cantidad_recibida', 'cantidad_devuelta', 'cantidad_conciliada',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cantidad_solicitada' => 'decimal:6',
            'cantidad_enviada' => 'decimal:6',
            'cantidad_recibida' => 'decimal:6',
            'cantidad_devuelta' => 'decimal:6',
            'cantidad_conciliada' => 'decimal:6',
        ];
    }

    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(TransferenciaExistencia::class, 'transferencia_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }
}
