<?php

namespace App\Models\Catalogo;

use App\Models\Organizacion\Empresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marca extends Model
{
    // Usa la tabla 'marcas' como origen de los registros de este modelo.
    protected $table = 'marcas';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['empresa_id', 'nombre', 'descripcion', 'logo_path', 'activo'];

    // Ejecuta la logica del metodo 'casts' dentro de este modelo.
    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime', 'actualizado_en' => 'datetime'];
    }

    // Define la relacion 'empresa' entre este modelo y los registros relacionados.
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Define la relacion 'productos' entre este modelo y los registros relacionados.
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'empresa_id', 'empresa_id')
            ->whereColumn('productos.marca', 'marcas.nombre');
    }
}
