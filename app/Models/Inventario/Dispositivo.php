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

/**
 * Define la responsabilidad de la clase Dispositivo dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class Dispositivo extends Model
{
    /** @use HasFactory<DispositivoFactory> */
    use HasFactory;

    /**
     * Ejecuta la operación newFactory definida por la clase Dispositivo.
     *
     * @return DispositivoFactory Resultado de la operación.
     */
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

    /**
     * Ejecuta la operación casts definida por la clase Dispositivo.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_salida' => 'datetime',
        ];
    }

    /**
     * Ejecuta la operación producto definida por la clase Dispositivo.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación variante definida por la clase Dispositivo.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }

    /**
     * Ejecuta la operación almacen definida por la clase Dispositivo.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_actual_id');
    }

    /**
     * Ejecuta la operación movimientos definida por la clase Dispositivo.
     *
     * @return HasMany Resultado de la operación.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoDispositivo::class, 'dispositivo_id');
    }

    /**
     * Ejecuta la operación ultimoMovimiento definida por la clase Dispositivo.
     *
     * @return HasOne Resultado de la operación.
     */
    public function ultimoMovimiento(): HasOne
    {
        return $this->hasOne(MovimientoDispositivo::class, 'dispositivo_id')->latestOfMany('creado_en');
    }

    /**
     * Ejecuta la operación identificadorLegible definida por la clase Dispositivo.
     *
     * @return string Resultado de la operación.
     */
    public function identificadorLegible(): string
    {
        $identificadores = array_filter(
            [$this->imei, $this->numero_serie],
            static fn (?string $valor): bool => is_string($valor) && trim($valor) !== '',
        );

        return $identificadores === [] ? 'Sin identificador' : implode(' · ', $identificadores);
    }
}
