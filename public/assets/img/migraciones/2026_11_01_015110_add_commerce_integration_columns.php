<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_01_015110_add_commerce_integration_columns.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        foreach (['categorias', 'productos', 'clientes', 'proveedores', 'campanas_publicitarias'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('empresa_id')->nullable()->index();
            });
        }

        Schema::table('producto_variantes', function (Blueprint $table): void {
            $table->string('codigo_sku', 80)->nullable()->unique();
            $table->string('codigo_barras', 80)->nullable()->unique();
            $table->boolean('controla_serie')->default(false);
        });

        Schema::table('compras', function (Blueprint $table): void {
            $table->string('numero_referencia', 100)->nullable();
            $table->char('moneda', 3)->default('PEN');
            $table->dateTime('actualizado_en', 6)->nullable();
        });

        Schema::table('cotizaciones', function (Blueprint $table): void {
            $table->char('moneda', 3)->default('PEN');
        });

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->integer('cliente_id')->nullable()->index();
            $table->integer('cotizacion_id')->nullable()->index();
            $table->string('canal', 30)->default('ecommerce');
            $table->foreignId('cupon_id')->nullable()->index();
            $table->foreignId('direccion_entrega_id')->nullable()->index();
            $table->foreignId('direccion_facturacion_id')->nullable()->index();
            $table->json('direccion_entrega_snapshot')->nullable();
            $table->json('direccion_facturacion_snapshot')->nullable();
        });

        Schema::table('compra_detalles', function (Blueprint $table): void {
            $table->integer('variante_id')->nullable()->index();
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuesto', 15, 2)->default(0);
            $table->decimal('total_linea', 15, 2)->nullable();
        });

        Schema::table('cotizacion_detalles', function (Blueprint $table): void {
            $table->integer('variante_id')->nullable()->index();
            $table->string('descripcion_snapshot')->nullable();
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuesto', 15, 2)->default(0);
            $table->decimal('total_linea', 15, 2)->nullable();
        });

        Schema::table('detalle_pedidos', function (Blueprint $table): void {
            $table->integer('variante_id')->nullable()->index();
            $table->string('descripcion_snapshot')->nullable();
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('impuesto', 15, 2)->default(0);
        });

        Schema::table('movimientos_inventario', function (Blueprint $table): void {
            $table->integer('variante_id')->nullable()->index();
            $table->string('documento_tipo', 60)->nullable();
            $table->string('documento_id', 80)->nullable();
            $table->index(['documento_tipo', 'documento_id']);
        });

        Schema::table('eventos_campana', function (Blueprint $table): void {
            $table->integer('pedido_id')->nullable()->index();
        });

        Schema::table('devoluciones_garantias', function (Blueprint $table): void {
            $table->foreignId('venta_id')->nullable()->index();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `movimientos_inventario` MODIFY `tipo_movimiento` VARCHAR(30) NOT NULL');
        }
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_01_015110_add_commerce_integration_columns.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('Commerce integration columns are forward-only to protect legacy data.');
    }
};
