<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_01_013915_create_rbac_seed_versions_table.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        Schema::create('rbac_seed_versions', function (Blueprint $table): void {
            $table->string('codigo', 80)->primary();
            $table->timestamp('aplicado_en')->useCurrent();
        });

        $catalogTables = [
            'roles', 'permisos', 'rol_permiso', 'roles_menus', 'menus', 'elementos_menu',
            'modulos', 'pantallas', 'usuario_roles', 'usuario_sede_roles',
        ];

        foreach ($catalogTables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                DB::table('rbac_seed_versions')->insertOrIgnore([
                    'codigo' => 'rbac-inicial-v1',
                    'aplicado_en' => now(),
                ]);

                break;
            }
        }
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_01_013915_create_rbac_seed_versions_table.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('RBAC seed history is forward-only because removing it could regrant revoked privileges.');
    }
};
