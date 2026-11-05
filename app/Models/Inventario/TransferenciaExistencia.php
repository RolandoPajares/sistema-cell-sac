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

class TransferenciaExistencia extends Model
{
    /** @use HasFactory<TransferenciaExistenciaFactory> */
    use HasFactory;

    protected $table = 'transferencias_existencias';

    protected $fillable = [
        'empresa_id', 'sede_origen_id', 'almacen_origen_id', 'sede_destino_id', 'almacen_destino_id',
        'codigo', 'estado', 'solicitado_por_usuario_id', 'aprobado_por_usuario_id', 'enviado_en', 'recibido_en', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['enviado_en' => 'datetime', 'recibido_en' => 'datetime', 'creado_en' => 'datetime'];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleTransferenciaExistencia::class, 'transferencia_id');
    }

    public function sedeOrigen(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_origen_id');
    }

    public function sedeDestino(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_destino_id');
    }

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por_usuario_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_usuario_id');
    }
}
