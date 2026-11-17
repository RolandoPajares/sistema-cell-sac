<?php

namespace App\Models\Inventario;

use App\Models\Organizacion\Almacen;
use App\Models\Organizacion\Sede;
use App\Models\User;
use Database\Factories\Inventario\TransferenciaExistenciaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase TransferenciaExistencia dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class TransferenciaExistencia extends Model
{
    /** @use HasFactory<TransferenciaExistenciaFactory> */
    use HasFactory;

    // Usa la tabla 'transferencias_existencias' como origen de los registros de este modelo.
    protected $table = 'transferencias_existencias';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'empresa_id', 'sede_origen_id', 'almacen_origen_id', 'sede_destino_id', 'almacen_destino_id',
        'codigo', 'estado', 'solicitado_por_usuario_id', 'aprobado_por_usuario_id', 'enviado_en', 'recibido_en', 'creado_en',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase TransferenciaExistencia.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['enviado_en' => 'datetime', 'recibido_en' => 'datetime', 'creado_en' => 'datetime'];
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return HasMany Resultado de la operación.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleTransferenciaExistencia::class, 'transferencia_id');
    }

    /**
     * Ejecuta la operación sedeOrigen definida por la clase TransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sedeOrigen(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_origen_id');
    }

    /**
     * Ejecuta la operación sedeDestino definida por la clase TransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sedeDestino(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_destino_id');
    }

    /**
     * Ejecuta la operación almacenOrigen definida por la clase TransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    /**
     * Ejecuta la operación almacenDestino definida por la clase TransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    /**
     * Ejecuta la operación solicitante definida por la clase TransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por_usuario_id');
    }

    /**
     * Ejecuta la operación aprobador definida por la clase TransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_usuario_id');
    }
}
