<?php

namespace App\Models\Catalogo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResenaProducto extends Model
{
    // Usa la tabla 'productos_resenas' como origen de los registros de este modelo.
    protected $table = 'productos_resenas';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['producto_id', 'usuario_id', 'valoracion', 'comentario'];

    // Ejecuta la logica del metodo 'casts' dentro de este modelo.
    protected function casts(): array
    {
        return ['valoracion' => 'integer'];
    }

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
