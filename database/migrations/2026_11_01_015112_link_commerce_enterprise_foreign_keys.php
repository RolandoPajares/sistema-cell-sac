<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (['categorias', 'productos', 'clientes', 'proveedores', 'campanas_publicitarias'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreign('empresa_id')->references('id')->on('empresas')->restrictOnDelete();
            });
        }

        Schema::table('compra_detalles', function (Blueprint $table): void {
            $table->foreign('variante_id')->references('id')->on('producto_variantes')->restrictOnDelete();
        });
        Schema::table('cotizacion_detalles', function (Blueprint $table): void {
            $table->foreign('variante_id')->references('id')->on('producto_variantes')->restrictOnDelete();
        });
        Schema::table('detalle_pedidos', function (Blueprint $table): void {
            $table->foreign('variante_id')->references('id')->on('producto_variantes')->restrictOnDelete();
        });
        Schema::table('movimientos_inventario', function (Blueprint $table): void {
            $table->foreign('variante_id')->references('id')->on('producto_variantes')->restrictOnDelete();
        });

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
            $table->foreign('cotizacion_id')->references('id')->on('cotizaciones')->restrictOnDelete();
            $table->foreign('cupon_id')->references('id')->on('cupones')->restrictOnDelete();
            $table->foreign(['cliente_id', 'direccion_entrega_id'])->references(['cliente_id', 'id'])->on('direcciones_cliente')->restrictOnDelete();
            $table->foreign(['cliente_id', 'direccion_facturacion_id'])->references(['cliente_id', 'id'])->on('direcciones_cliente')->restrictOnDelete();
        });

        Schema::table('eventos_campana', function (Blueprint $table): void {
            $table->foreign('pedido_id')->references('id')->on('pedidos')->nullOnDelete();
        });
        Schema::table('devoluciones_garantias', function (Blueprint $table): void {
            $table->foreign('venta_id')->references('id')->on('ventas')->restrictOnDelete();
        });
        Schema::table('ventas', function (Blueprint $table): void {
            $table->foreign('sesion_caja_id')->references('id')->on('sesiones_caja')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        throw new LogicException('Enterprise relationships are forward-only to protect business records.');
    }
};
