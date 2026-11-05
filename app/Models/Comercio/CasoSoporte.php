<?php

namespace App\Models\Comercio;

use App\Models\Clientes\Cliente;
use App\Models\Organizacion\Sede;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CasoSoporte extends Model
{
    protected $table = 'casos_soporte';

    protected $fillable = [
        'empresa_id', 'sede_id', 'cliente_id', 'asignado_a_usuario_id', 'codigo', 'asunto', 'estado', 'prioridad',
        'creado_en', 'actualizado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['creado_en' => 'datetime', 'actualizado_en' => 'datetime'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_usuario_id');
    }
}
