<?php

namespace App\Models\Catalogo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoritoProducto extends Model
{
    // Usa la tabla 'productos_favoritos' como origen de los registros de este modelo.
    protected $table = 'productos_favoritos';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['producto_id', 'usuario_id'];

    // Define la relacion 'product' entre este modelo y los registros relacionados.
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Define la relacion 'user' entre este modelo y los registros relacionados.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
