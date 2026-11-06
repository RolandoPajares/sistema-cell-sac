<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_05_130000_reduce_security_catalog_to_six_interfaces.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (! Schema::hasTable('pantallas') || ! Schema::hasTable('permisos') || ! Schema::hasTable('elementos_menu')) {
            return;
        }

        DB::transaction(function (): void {
            $removedCodes = ['admin.delegation', 'admin.sessions', 'admin.account_status'];
            $removedScreens = DB::table('pantallas')->whereIn('codigo', $removedCodes)->get(['id', 'codigo', 'nombre']);
            $removedScreenIds = $removedScreens->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
            if ($removedScreenIds !== []) {
                DB::table('elementos_menu')->whereIn('pantalla_id', $removedScreenIds)->delete();
                // El rol administrador está protegido por un trigger que impide
                // retirar sus permisos. Se conservan esas filas históricas, pero
                // las pantallas quedan inactivas y fuera del menú dinámico.
                DB::table('pantallas')->whereIn('id', $removedScreenIds)->update([
                    'habilitada' => false,
                    'actualizado_en' => now(),
                ]);
            }

            $moduleId = DB::table('modulos')->where('codigo', 'seguridad')->value('id');
            if ($moduleId === null) {
                return;
            }

            $screenId = DB::table('pantallas')->where('codigo', 'admin.permissions_matrix')->value('id');
            if ($screenId === null) {
                $screenId = DB::table('pantallas')->insertGetId([
                    'modulo_id' => $moduleId,
                    'codigo' => 'admin.permissions_matrix',
                    'nombre' => 'Matriz de privilegios',
                    'ruta' => 'management.permissions_matrix',
                    'icono' => 'bi-grid-3x3-gap',
                    'habilitada' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            } else {
                DB::table('pantallas')->where('id', $screenId)->update([
                    'modulo_id' => $moduleId,
                    'ruta' => 'management.permissions_matrix',
                    'nombre' => 'Matriz de privilegios',
                    'icono' => 'bi-grid-3x3-gap',
                    'habilitada' => true,
                    'actualizado_en' => now(),
                ]);
            }

            $permissionCode = 'pantalla.admin.permissions_matrix.consultar';
            $permissionId = DB::table('permisos')->where('codigo', $permissionCode)->value('id');
            if ($permissionId === null) {
                $permissionId = DB::table('permisos')->insertGetId([
                    'modulo_id' => $moduleId,
                    'pantalla_id' => $screenId,
                    'codigo' => $permissionCode,
                    'accion' => 'consultar',
                    'nombre' => 'Matriz de privilegios - consultar',
                    'es_sistema' => true,
                    'activo' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            } else {
                DB::table('permisos')->where('id', $permissionId)->update([
                    'modulo_id' => $moduleId,
                    'pantalla_id' => $screenId,
                    'activo' => true,
                    'actualizado_en' => now(),
                ]);
            }

            $adminRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
            if ($adminRoleId !== null && Schema::hasTable('roles_permisos')) {
                DB::table('roles_permisos')->insertOrIgnore([
                    'rol_id' => $adminRoleId,
                    'permiso_id' => $permissionId,
                    'alcance' => 'empresa',
                    'creado_en' => now(),
                ]);
            }

            $menuId = DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');
            $parentId = $menuId === null ? null : DB::table('elementos_menu')->where('menu_id', $menuId)->where('codigo', 'grupo.seguridad')->value('id');
            if ($menuId !== null && $parentId !== null) {
                DB::table('elementos_menu')->updateOrInsert(
                    ['menu_id' => $menuId, 'codigo' => 'admin.permissions_matrix'],
                    [
                        'padre_id' => $parentId,
                        'pantalla_id' => $screenId,
                        'permiso_id' => $permissionId,
                        'etiqueta' => 'Matriz de privilegios',
                        'icono' => 'bi-grid-3x3-gap',
                        'tipo' => 'pantalla',
                        'orden' => 3,
                        'habilitado' => true,
                        'actualizado_en' => now(),
                        'creado_en' => now(),
                    ],
                );

                $orders = [
                    'admin.users' => 1,
                    'admin.roles' => 2,
                    'admin.permissions_matrix' => 3,
                    'admin.user_assignments' => 4,
                    'admin.menus' => 5,
                    'admin.modules' => 6,
                ];
                foreach ($orders as $code => $order) {
                    DB::table('elementos_menu')->where('menu_id', $menuId)->where('codigo', $code)->update(['padre_id' => $parentId, 'orden' => $order, 'habilitado' => true, 'actualizado_en' => now()]);
                }
            }

            if (Schema::hasTable('registros_auditoria') && ($removedScreens->isNotEmpty() || $screenId !== null)) {
                DB::table('registros_auditoria')->insert([
                    'accion' => 'catalogo_seguridad_reducido_seis_interfaces',
                    'entidad' => 'pantallas',
                    'valores_anteriores' => json_encode(['deshabilitadas' => $removedScreens->values()->all()], JSON_UNESCAPED_UNICODE),
                    'valores_nuevos' => json_encode(['agregada' => 'admin.permissions_matrix', 'interfaces_seguridad' => ['admin.users', 'admin.roles', 'admin.permissions_matrix', 'admin.user_assignments', 'admin.menus', 'admin.modules']], JSON_UNESCAPED_UNICODE),
                    'creado_en' => now(),
                    'identificador_universal_evento' => (string) Str::uuid(),
                    'modulo' => 'seguridad',
                    'resultado' => 'exito',
                    'origen' => 'laravel',
                    'tabla_afectada' => 'pantallas',
                    'observaciones' => 'Limpieza autorizada del catálogo de Seguridad y Delegación para conservar únicamente las seis interfaces operativas.',
                    'ocurrido_en' => now(),
                ]);
            }
        });
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_05_130000_reduce_security_catalog_to_six_interfaces.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('This RBAC catalog cleanup is forward-only.');
    }
};
