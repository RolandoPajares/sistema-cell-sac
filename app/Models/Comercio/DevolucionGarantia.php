<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionGarantia extends Model
{
    protected $table = 'devoluciones_garantias';

    protected $fillable = [
        'codigo',
        'pedido_id',
        'cliente_id',
        'producto_id',
        'tipo',
        'motivo',
        'estado',
        'fecha_solicitud',
        'fecha_limite',
        'resolucion',
        'creado_por',
        'activo',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'date',
            'fecha_limite' => 'date',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
