<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RbacInicialSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Define la responsabilidad de la clase SpanishAccessFixture dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class SpanishAccessFixture
{
    /** @return array{empresa_id:int,sede_id:int,almacen_id:int} */
    public static function organization(): array
    {
        if (! DB::table('versiones_inicializacion_control_acceso')->where('codigo', 'rbac-inicial-v1')->exists()) {
            app(RbacInicialSeeder::class)->run();
        }

        $companyId = DB::table('empresas')->where('codigo', 'PRUEBA')->value('id');
        if ($companyId === null) {
            $companyId = DB::table('empresas')->insertGetId([
                'codigo' => 'PRUEBA', 'razon_social' => 'Empresa sintética de pruebas',
                'activo' => true, 'creado_en' => now(), 'actualizado_en' => now(),
            ]);
        }

        $siteId = DB::table('sedes')->where('empresa_id', $companyId)->where('codigo', 'PRINCIPAL')->value('id');
        if ($siteId === null) {
            $siteId = DB::table('sedes')->insertGetId([
                'empresa_id' => $companyId, 'codigo' => 'PRINCIPAL', 'nombre' => 'Sede sintética',
                'activa' => true, 'creado_en' => now(), 'actualizado_en' => now(),
            ]);
        }

        $warehouseId = DB::table('almacenes')->where('sede_id', $siteId)->where('codigo', 'PRINCIPAL')->value('id');
        if ($warehouseId === null) {
            $warehouseId = DB::table('almacenes')->insertGetId([
                'sede_id' => $siteId, 'codigo' => 'PRINCIPAL', 'nombre' => 'Almacén sintético',
                'activo' => true, 'creado_en' => now(), 'actualizado_en' => now(),
            ]);
        }

        return ['empresa_id' => (int) $companyId, 'sede_id' => (int) $siteId, 'almacen_id' => (int) $warehouseId];
    }

    /**
     * Ejecuta la operación assign definida por la clase SpanishAccessFixture.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $roleCode  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public static function assign(User $user, string $roleCode): void
    {
        $organization = self::organization();
        $role = DB::table('roles')->where('codigo', $roleCode)->first(['id', 'alcance_permitido']);
        if ($role === null) {
            throw new \RuntimeException('El rol sintético no existe: '.$roleCode);
        }

        if ($role->alcance_permitido === 'sede') {
            DB::table('usuarios_sedes_roles')->insertOrIgnore([
                'usuario_id' => $user->id, 'sede_id' => $organization['sede_id'],
                'rol_id' => $role->id, 'alcance_asignacion' => 'sede',
                'asignado_en' => now(), 'activo' => true,
            ]);

            return;
        }

        DB::table('usuarios_roles')->insertOrIgnore([
            'usuario_id' => $user->id, 'rol_id' => $role->id,
            'asignado_en' => now(), 'activo' => true,
        ]);
    }

    /**
     * Ejecuta la operación user definida por la clase SpanishAccessFixture.
     *
     * @param  mixed  $roleCode  Parámetro recibido por la operación.
     * @return User Resultado de la operación.
     */
    public static function user(string $roleCode): User
    {
        $user = User::factory()->create(['rol' => $roleCode]);
        self::assign($user, $roleCode);

        return $user;
    }

    /**
     * Ejecuta la operación grantScreenReadPermission definida por la clase SpanishAccessFixture.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $screenCode  Parámetro recibido por la operación.
     * @param  mixed  $routeName  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public static function grantScreenReadPermission(User $user, string $screenCode, string $routeName): void
    {
        $screen = DB::table('pantallas')->where('codigo', $screenCode)->first();
        if ($screen === null) {
            throw new \RuntimeException('La pantalla de prueba no existe: '.$screenCode);
        }

        DB::table('pantallas')->where('id', $screen->id)->update([
            'ruta' => $routeName,
            'habilitada' => true,
        ]);

        $permissionCode = 'pantalla.'.$screenCode.'.consultar';
        $permissionId = DB::table('permisos')->where('codigo', $permissionCode)->value('id');
        if ($permissionId === null) {
            $permissionId = DB::table('permisos')->insertGetId([
                'modulo_id' => $screen->modulo_id,
                'pantalla_id' => $screen->id,
                'codigo' => $permissionCode,
                'accion' => 'consultar',
                'nombre' => $screen->nombre.' - consultar',
                'es_sistema' => true,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
        } else {
            DB::table('permisos')->where('id', $permissionId)->update(['activo' => true]);
        }

        $rolAdministradorId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        DB::table('roles_permisos')->insertOrIgnore([
            'rol_id' => $rolAdministradorId,
            'permiso_id' => $permissionId,
            'alcance' => 'empresa',
            'creado_en' => now(),
        ]);

        $menuId = DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');
        DB::table('elementos_menu')
            ->where('menu_id', $menuId)
            ->where('pantalla_id', $screen->id)
            ->update(['permiso_id' => $permissionId, 'habilitado' => true]);
    }
}
