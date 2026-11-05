<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\ElementoMenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElementoMenu extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<ElementoMenuFactory> */
    use HasFactory;

    protected $table = 'elementos_menu';

    protected $fillable = ['menu_id', 'padre_id', 'pantalla_id', 'permiso_id', 'codigo', 'etiqueta', 'icono', 'tipo', 'orden', 'habilitado'];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'habilitado' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    public function elementosHijos(): HasMany
    {
        return $this->hasMany(self::class, 'padre_id')->orderBy('orden');
    }

    public function pantalla(): BelongsTo
    {
        return $this->belongsTo(Pantalla::class, 'pantalla_id');
    }

    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'permiso_id');
    }
}
