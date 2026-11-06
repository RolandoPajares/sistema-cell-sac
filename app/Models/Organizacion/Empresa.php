<?php

namespace App\Models\Organizacion;

use Database\Factories\Organizacion\EmpresaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Empresa dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Empresa extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<EmpresaFactory> */
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = ['codigo', 'razon_social', 'nombre_comercial', 'ruc', 'moneda_base', 'activo'];

    /**
     * Ejecuta la operación casts definida por la clase Empresa.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Ejecuta la operación sedes definida por la clase Empresa.
     *
     * @return HasMany Resultado de la operación.
     */
    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class, 'empresa_id')->orderBy('nombre');
    }
}
