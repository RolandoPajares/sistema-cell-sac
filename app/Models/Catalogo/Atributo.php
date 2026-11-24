<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atributo extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'atributos';

    protected $fillable = [
        'nombre', 'tipo_dato', 'unidad_medida', 'activo', 'visible', 'filtrable',
        'orden', 'tipo_control', 'dependencia_atributo_id', 'empresa_id',
    ];

    public function valores(): HasMany
    {
        return $this->hasMany(AtributoValor::class, 'atributo_id')->orderBy('orden')->orderBy('id');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_atributo', 'atributo_id', 'categoria_id')
            ->withPivot(['activo', 'orden']);
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(self::class, 'dependencia_atributo_id');
    }

    public function dependientes(): HasMany
    {
        return $this->hasMany(self::class, 'dependencia_atributo_id');
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'visible' => 'boolean',
            'filtrable' => 'boolean',
            'orden' => 'integer',
        ];
    }
}
