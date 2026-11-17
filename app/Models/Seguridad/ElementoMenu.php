<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\ElementoMenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase ElementoMenu dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ElementoMenu extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<ElementoMenuFactory> */
    use HasFactory;

    // Usa la tabla 'elementos_menu' como origen de los registros de este modelo.
    protected $table = 'elementos_menu';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['menu_id', 'padre_id', 'pantalla_id', 'permiso_id', 'codigo', 'etiqueta', 'icono', 'tipo', 'orden', 'habilitado'];

    /**
     * Ejecuta la operación casts definida por la clase ElementoMenu.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'habilitado' => 'boolean',
        ];
    }

    /**
     * Ejecuta la operación menu definida por la clase ElementoMenu.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Ejecuta la operación padre definida por la clase ElementoMenu.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    /**
     * Ejecuta la operación elementosHijos definida por la clase ElementoMenu.
     *
     * @return HasMany Resultado de la operación.
     */
    public function elementosHijos(): HasMany
    {
        return $this->hasMany(self::class, 'padre_id')->orderBy('orden');
    }

    /**
     * Ejecuta la operación pantalla definida por la clase ElementoMenu.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function pantalla(): BelongsTo
    {
        return $this->belongsTo(Pantalla::class, 'pantalla_id');
    }

    /**
     * Ejecuta la operación permiso definida por la clase ElementoMenu.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'permiso_id');
    }
}
