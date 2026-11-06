<?php

namespace App\Models\Catalogo;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ResenaProducto extends Model
{
    protected $table = 'productos_resenas';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = ['producto_id', 'usuario_id', 'valoracion', 'comentario'];

    protected function casts(): array
    {
        return ['valoracion' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
