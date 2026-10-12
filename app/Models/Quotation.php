<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = ['cliente_id', 'creado_por', 'total', 'estado', 'notas', 'activo', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function details(): HasMany
    {
        return $this->hasMany(QuotationDetail::class, 'cotizacion_id');
    }
}
