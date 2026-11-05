<?php

namespace App\Models\Inventario;

use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use App\Models\Organizacion\Almacen;
use Database\Factories\Inventario\DispositivoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Dispositivo extends Model
{
    /** @use HasFactory<DispositivoFactory> */
    use HasFactory;

    protected static function newFactory(): DispositivoFactory
    {
        return DispositivoFactory::new();
    }

    protected $table = 'dispositivos';

    protected $fillable = [
        'producto_id', 'variante_id', 'imei', 'numero_serie', 'detalle_compra_id',
        'almacen_actual_id', 'estado', 'fecha_ingreso', 'fecha_salida',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_salida' => 'datetime',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_actual_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoDispositivo::class, 'dispositivo_id');
    }

    public function ultimoMovimiento(): HasOne
    {
        return $this->hasOne(MovimientoDispositivo::class, 'dispositivo_id')->latestOfMany('creado_en');
    }

    public function identificadorLegible(): string
    {
        $identificadores = array_filter(
            [$this->imei, $this->numero_serie],
            static fn (?string $valor): bool => is_string($valor) && trim($valor) !== '',
        );

        return $identificadores === [] ? 'Sin identificador' : implode(' · ', $identificadores);
    }
}
