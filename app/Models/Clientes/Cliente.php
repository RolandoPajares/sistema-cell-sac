<?php

namespace App\Models\Clientes;

use App\Models\Comercio\Cotizacion;
use App\Models\Organizacion\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Cliente dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'usuario_id', 'empresa_id', 'tipo', 'tipo_documento', 'documento', 'razon_social', 'nombre_contacto',
        'correo', 'telefono', 'ciudad', 'activo', 'creado_en',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Cliente.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación user definida por la clase Cliente.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Ejecuta la operación quotations definida por la clase Cliente.
     *
     * @return HasMany Resultado de la operación.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'cliente_id');
    }
}
