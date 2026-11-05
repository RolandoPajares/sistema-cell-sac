<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, array{route:string,name:string}> */
    private array $screens = [
        'admin.shipments' => ['route' => 'management.inventory.preview.shipments', 'name' => 'Envíos y entregas - consultar'],
        'admin.imei' => ['route' => 'management.inventory.preview.serials', 'name' => 'IMEI y números de serie - consultar'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('pantallas') || ! Schema::hasTable('permisos') || ! Schema::hasTable('roles_permisos')) {
            return;
        }

        $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        $moduleId = DB::table('modulos')->where('codigo', 'inventario')->value('id');
        if ($adminRoleId === null || $moduleId === null) {
            return;
        }

        foreach ($this->screens as $screenCode => $definition) {
            $screen = DB::table('pantallas')->where('codigo', $screenCode)->first();
            if ($screen === null) {
                continue;
            }
            DB::table('pantallas')->where('id', $screen->id)->update([
                'ruta' => $definition['route'],
                'habilitada' => true,
                'actualizado_en' => now(),
            ]);

            $permissionCode = 'pantalla.'.$screenCode.'.consultar';
            $permission = DB::table('permisos')->where('codigo', $permissionCode)->first();
            $permissionId = $permission?->id;
            if ($permissionId === null) {
                $permissionId = DB::table('permisos')->insertGetId([
                    'modulo_id' => $moduleId,
                    'pantalla_id' => $screen->id,
                    'codigo' => $permissionCode,
                    'accion' => 'consultar',
                    'nombre' => $definition['name'],
                    'es_sistema' => true,
                    'activo' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            } else {
                DB::table('permisos')->where('id', $permissionId)->update([
                    'modulo_id' => $moduleId,
                    'pantalla_id' => $screen->id,
                    'accion' => 'consultar',
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
            if (Schema::hasTable('elementos_menu')) {
                DB::table('elementos_menu')->where('codigo', $screenCode)->update([
                    'pantalla_id' => $screen->id,
                    'permiso_id' => $permissionId,
                    'habilitado' => true,
                    'actualizado_en' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        throw new LogicException('This RBAC repair is forward-only to avoid revoking administrator permissions.');
    }
};
