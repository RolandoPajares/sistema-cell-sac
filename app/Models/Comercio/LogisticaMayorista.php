<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticaMayorista extends Model
{
    protected $table = 'logistica_mayorista';

    protected $primaryKey = 'pedido_id';

    protected $fillable = [
        'pedido_id', 'exportador', 'direccion_exportador', 'contacto_exportador',
        'direccion_consignatario', 'contacto_consignatario', 'factura_numero',
        'factura_fecha', 'orden_compra', 'carta_credito', 'fecha_emision',
        'pais_origen', 'lugar_carga', 'puerto_descarga', 'direccion_entrega',
        'descripcion_mercancia', 'codigo_hs', 'cantidad_unidades', 'bultos',
        'tipo_embalaje', 'dimensiones', 'volumen_m3', 'peso_neto', 'peso_bruto',
    ];

    public $incrementing = false;

    public $timestamps = false;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
