<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = ['codigo', 'nombre', 'contexto', 'habilitado'];

    protected function casts(): array
    {
        return ['habilitado' => 'boolean'];
    }

    public function elementos(): HasMany
    {
        return $this->hasMany(ElementoMenu::class, 'menu_id')->whereNull('padre_id')->orderBy('orden');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'roles_menus', 'menu_id', 'rol_id');
    }
}
