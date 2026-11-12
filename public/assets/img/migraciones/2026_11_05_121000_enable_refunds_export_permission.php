<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_05_121000_enable_refunds_export_permission.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (! Schema::hasTable('modulos') || ! Schema::hasTable('permisos') || ! Schema::hasTable('roles_permisos')) {
            return;
        }

        $moduleId = DB::table('modulos')->where('codigo', 'reembolsos')->value('id');
        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        if ($moduleId === null || $adminRoleId === null) {
            return;
        }

        $permissionCode = 'gestion.reembolsos.exportar';
        $permission = DB::table('permisos')->where('codigo', $permissionCode)->first();
        $permissionId = $permission?->id;
        if ($permissionId === null) {
            $permissionId = DB::table('permisos')->insertGetId([
                'modulo_id' => $moduleId,
                'pantalla_id' => null,
                'codigo' => $permissionCode,
                'accion' => 'exportar',
                'nombre' => 'Exportar - Reembolsos',
                'es_sistema' => true,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        } else {
            DB::table('permisos')->where('id', $permissionId)->update([
                'modulo_id' => $moduleId,
                'pantalla_id' => null,
                'accion' => 'exportar',
                'activo' => true,
                'actualizado_en' => now(),
            ]);
        }

        DB::table('roles_permisos')->insertOrIgnore([
            'rol_id' => $adminRoleId,
            'permiso_id' => $permissionId,
            'alcance' => 'empresa',
            'creado_en' => now(),
        ]);
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_05_121000_enable_refunds_export_permission.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('This RBAC repair is forward-only to avoid revoking administrator permissions.');
    }
};
