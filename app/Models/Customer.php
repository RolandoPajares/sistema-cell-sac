<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'usuario_id', 'tipo', 'documento', 'razon_social', 'nombre_contacto',
        'correo', 'telefono', 'ciudad', 'activo', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'cliente_id');
    }
}
