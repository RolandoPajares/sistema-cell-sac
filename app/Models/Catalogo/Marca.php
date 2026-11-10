<?php

namespace App\Models\Catalogo;

use App\Models\Organizacion\Empresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marca extends Model
{
    protected $table = 'marcas';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = ['empresa_id', 'nombre', 'descripcion', 'logo_path', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime', 'actualizado_en' => 'datetime'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'empresa_id', 'empresa_id')
            ->whereColumn('productos.marca', 'marcas.nombre');
    }
}
