<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Crea únicamente la organización inicial, sin usuarios ni datos comerciales.
 */
class InstalacionLimpiaSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::connection()->getDatabaseName() !== 'cell_sac_limpia') {
            throw new RuntimeException('La instalación limpia solo puede ejecutarse en cell_sac_limpia.');
        }

        foreach (['empresas', 'sedes', 'almacenes', 'usuarios', 'ventas', 'compras', 'productos', 'pedidos'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException('Falta la tabla requerida: '.$table.'.');
            }
        }

        if (DB::table('usuarios')->exists() || DB::table('ventas')->exists() || DB::table('compras')->exists()
            || DB::table('productos')->exists() || DB::table('pedidos')->exists()) {
            throw new RuntimeException('La base ya contiene usuarios o registros comerciales.');
        }

        DB::transaction(function (): void {
            $companyId = DB::table('empresas')->where('codigo', 'CELL-SAC')->value('id');
            if ($companyId === null) {
                $companyId = DB::table('empresas')->insertGetId([
                    'codigo' => 'CELL-SAC',
                    'razon_social' => 'Sistema Cell SAC',
                    'nombre_comercial' => 'Cell SAC',
                    'moneda_base' => 'PEN',
                    'activo' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            }

            $siteId = DB::table('sedes')->where('empresa_id', $companyId)->where('codigo', 'PRINCIPAL')->value('id');
            if ($siteId === null) {
                $siteId = DB::table('sedes')->insertGetId([
                    'empresa_id' => $companyId,
                    'codigo' => 'PRINCIPAL',
                    'nombre' => 'Sede principal',
                    'zona_horaria' => 'America/Lima',
                    'activa' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            }

            if (! DB::table('almacenes')->where('sede_id', $siteId)->where('codigo', 'PRINCIPAL')->exists()) {
                DB::table('almacenes')->insert([
                    'sede_id' => $siteId,
                    'codigo' => 'PRINCIPAL',
                    'nombre' => 'Almacén principal',
                    'tipo' => 'general',
                    'permite_venta' => true,
                    'activo' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            }
        });

        $this->call(RbacInicialSeeder::class);
    }
}
