<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_01_015111_backfill_main_organization_and_stock.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::transaction(function (): void {
            DB::table('empresas')->insertOrIgnore([
                'codigo' => 'TECHCELL',
                'razon_social' => 'Technology CELL SAC',
                'nombre_comercial' => 'Technology CELL',
                'moneda_base' => 'PEN',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $companyId = (int) DB::table('empresas')->where('codigo', 'TECHCELL')->value('id');
            if ($companyId === 0) {
                throw new RuntimeException('The main company could not be identified.');
            }

            DB::table('sedes')->insertOrIgnore([
                'empresa_id' => $companyId,
                'codigo' => 'PRINCIPAL',
                'nombre' => 'Sede principal',
                'zona_horaria' => 'America/Lima',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $siteId = (int) DB::table('sedes')->where('empresa_id', $companyId)->where('codigo', 'PRINCIPAL')->value('id');
            if ($siteId === 0 || DB::table('sedes')->where('empresa_id', $companyId)->count() !== 1) {
                throw new RuntimeException('Legacy operations require one unambiguous site before backfill.');
            }

            DB::table('almacenes')->insertOrIgnore([
                'sede_id' => $siteId,
                'codigo' => 'PRINCIPAL',
                'nombre' => 'Almacén principal',
                'tipo' => 'general',
                'permite_venta' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $warehouseId = (int) DB::table('almacenes')->where('sede_id', $siteId)->where('codigo', 'PRINCIPAL')->value('id');
            if ($warehouseId === 0 || DB::table('almacenes')->where('sede_id', $siteId)->count() !== 1) {
                throw new RuntimeException('Legacy stock requires one unambiguous warehouse before backfill.');
            }

            foreach (['categorias', 'productos', 'clientes', 'proveedores', 'campanas_publicitarias'] as $tableName) {
                DB::table($tableName)->whereNull('empresa_id')->update(['empresa_id' => $companyId]);
            }

            foreach (['pedidos', 'compras', 'cotizaciones', 'movimientos_inventario', 'devoluciones_garantias'] as $tableName) {
                DB::table($tableName)->whereNull('empresa_id')->update(['empresa_id' => $companyId]);
                DB::table($tableName)->whereNull('sede_id')->update(['sede_id' => $siteId]);
            }

            foreach (['pedidos', 'compras', 'movimientos_inventario'] as $tableName) {
                DB::table($tableName)->whereNull('almacen_id')->update(['almacen_id' => $warehouseId]);
            }

            if (DB::table('movimientos_inventario')->exists()) {
                throw new RuntimeException('Existing stock movements require a reviewed opening-balance reconciliation.');
            }

            $mismatchedStock = DB::table('productos as p')
                ->whereExists(function ($query): void {
                    $query->selectRaw('1')->from('producto_variantes as v')->whereColumn('v.producto_id', 'p.id');
                })
                ->whereRaw('p.existencias <> (SELECT COALESCE(SUM(v.stock), 0) FROM producto_variantes v WHERE v.producto_id = p.id)')
                ->exists();

            if ($mismatchedStock) {
                throw new RuntimeException('Product and variant stock do not reconcile; no opening balances were created.');
            }

            DB::statement("UPDATE producto_variantes SET codigo_sku = CONCAT('LEGACY-V-', id) WHERE codigo_sku IS NULL");
            DB::statement("INSERT INTO producto_variantes(producto_id, codigo_sku, nombre_color, codigo_color, stock, controla_serie, estado, fecha_creacion)
                SELECT p.id, CONCAT('LEGACY-P-', p.id), COALESCE(NULLIF(p.color, ''), 'Único'), NULL, p.existencias, 0, 'activo', CURRENT_TIMESTAMP
                FROM productos p WHERE NOT EXISTS (SELECT 1 FROM producto_variantes v WHERE v.producto_id = p.id)");
            DB::statement('INSERT INTO existencias_almacen(almacen_id, variante_id, cantidad_fisica, cantidad_reservada)
                SELECT ?, v.id, v.stock, 0 FROM producto_variantes v', [$warehouseId]);
            DB::statement("INSERT INTO movimientos_inventario(producto_id, usuario_id, tipo_movimiento, cantidad, notas, empresa_id, sede_id, almacen_id, variante_id, documento_tipo, documento_id)
                SELECT v.producto_id, NULL, 'entrada', v.stock, 'Saldo de apertura migrado desde existencias heredadas.', ?, ?, ?, v.id, 'saldo_apertura', 'TECHCELL-ERP-OPENING'
                FROM producto_variantes v WHERE v.stock > 0", [$companyId, $siteId, $warehouseId]);

            DB::table('pedidos')->whereNull('cliente_id')->whereIn('usuario_id', DB::table('clientes')->whereNotNull('usuario_id')->select('usuario_id'))
                ->orderBy('id')->chunkById(500, function ($orders): void {
                    foreach ($orders as $order) {
                        $customerId = DB::table('clientes')->where('usuario_id', $order->usuario_id)->value('id');
                        if ($customerId !== null) {
                            DB::table('pedidos')->where('id', $order->id)->update(['cliente_id' => $customerId]);
                        }
                    }
                });
        });
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_01_015111_backfill_main_organization_and_stock.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('The opening-balance backfill is forward-only and preserves legacy rows.');
    }
};
