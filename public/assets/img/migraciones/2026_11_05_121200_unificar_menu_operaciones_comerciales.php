<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Deja únicamente las siete opciones comerciales autorizadas en el menú del administrador.
     * Las pantallas, permisos, roles y rutas se conservan para no alterar las reglas de negocio.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $menuId = DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');
            if ($menuId === null) {
                return;
            }

            $operationsGroupId = DB::table('elementos_menu')
                ->where('menu_id', $menuId)
                ->where('codigo', 'grupo.operaciones')
                ->value('id');

            if ($operationsGroupId !== null) {
                DB::table('elementos_menu')
                    ->where('menu_id', $menuId)
                    ->where('padre_id', $operationsGroupId)
                    ->whereIn('codigo', ['admin.wholesale_sales', 'support.reclamos'])
                    ->delete();
            }

            DB::table('elementos_menu')
                ->where('codigo', 'admin.retail_sales')
                ->update(['etiqueta' => 'Ventas', 'actualizado_en' => now()]);

            DB::table('pantallas')
                ->where('codigo', 'admin.retail_sales')
                ->update(['nombre' => 'Ventas', 'actualizado_en' => now()]);

            DB::table('elementos_menu')
                ->where('codigo', 'admin.online_orders')
                ->update(['etiqueta' => 'Pedidos', 'actualizado_en' => now()]);

            DB::table('pantallas')
                ->where('codigo', 'admin.online_orders')
                ->update(['nombre' => 'Pedidos', 'actualizado_en' => now()]);
        });
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_05_121200_unificar_menu_operaciones_comerciales.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('La unificación del menú comercial es deliberadamente irreversible; no se recrean opciones eliminadas automáticamente.');
    }
};
