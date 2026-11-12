<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Menu dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Menu extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    // Usa la tabla 'menus' como origen de los registros de este modelo.
    protected $table = 'menus';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['codigo', 'nombre', 'contexto', 'habilitado'];

    /**
     * Ejecuta la operación casts definida por la clase Menu.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['habilitado' => 'boolean'];
    }

    /**
     * Ejecuta la operación elementos definida por la clase Menu.
     *
     * @return HasMany Resultado de la operación.
     */
    public function elementos(): HasMany
    {
        return $this->hasMany(ElementoMenu::class, 'menu_id')->whereNull('padre_id')->orderBy('orden');
    }

    /**
     * Ejecuta la operación roles definida por la clase Menu.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'roles_menus', 'menu_id', 'rol_id');
    }
}
