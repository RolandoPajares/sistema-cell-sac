<?php

namespace App\Models\Marketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoCampania extends Model
{
    protected $table = 'eventos_campana';

    protected $fillable = ['campana_id', 'usuario_id', 'tipo', 'sesion_hash', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campania::class, 'campana_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
