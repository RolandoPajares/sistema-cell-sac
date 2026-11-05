<?php

namespace App\Models\Finanzas;

use App\Models\Organizacion\Empresa;
use Database\Factories\Finanzas\CuentaBancariaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaBancaria extends Model
{
    /** @use HasFactory<CuentaBancariaFactory> */
    use HasFactory;

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'empresa_id', 'codigo', 'banco', 'tipo_cuenta', 'moneda',
        'cuenta_mascara', 'referencia_secreto', 'activa',
    ];

    public $timestamps = false;

    protected $hidden = ['referencia_secreto'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
