<?php

namespace App\Models\Organizacion;

use Database\Factories\Organizacion\EmpresaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<EmpresaFactory> */
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = ['codigo', 'razon_social', 'nombre_comercial', 'ruc', 'moneda_base', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class, 'empresa_id')->orderBy('nombre');
    }
}
