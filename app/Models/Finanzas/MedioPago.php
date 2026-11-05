<?php

namespace App\Models\Finanzas;

use Database\Factories\Finanzas\MedioPagoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedioPago extends Model
{
    /** @use HasFactory<MedioPagoFactory> */
    use HasFactory;

    protected $table = 'medios_pago';

    protected $fillable = ['codigo', 'nombre', 'requiere_caja', 'requiere_banco', 'activo'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'requiere_caja' => 'boolean',
            'requiere_banco' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'medio_pago_id');
    }
}
