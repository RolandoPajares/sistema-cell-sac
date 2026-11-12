<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alinea los nombres heredados del catálogo con los nombres utilizados por
     * los modelos Eloquent y el esquema empresarial vigente.
     *
     * La operación es deliberadamente idempotente: una base que ya contiene los
     * nombres actuales no se modifica.
     */
    public function up(): void
    {
        $this->renameIfNeeded('producto_variantes', 'variantes_productos');
        $this->renameIfNeeded('producto_imagen_referencia', 'referencias_imagenes_productos');
        $this->renameIfNeeded('variante_imagenes', 'imagenes_variantes');

        if (Schema::hasTable('variantes_productos')
            && Schema::hasColumn('variantes_productos', 'stock')
            && ! Schema::hasColumn('variantes_productos', 'existencias')) {
            Schema::table('variantes_productos', function (Blueprint $table): void {
                $table->renameColumn('stock', 'existencias');
            });
        }
    }

    /**
     * Las migraciones posteriores y los modelos ya dependen de los nombres
     * normalizados; no se revierte para evitar romper claves foráneas y datos.
     */
    public function down(): void
    {
        throw new LogicException('La normalización de tablas del catálogo es forward-only para proteger los datos.');
    }

    private function renameIfNeeded(string $legacy, string $current): void
    {
        if (Schema::hasTable($legacy) && ! Schema::hasTable($current)) {
            Schema::rename($legacy, $current);
        }
    }
};
