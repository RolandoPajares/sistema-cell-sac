<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RbacInicialSeeder;
use Illuminate\Support\Facades\DB;

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

    public static function user(string $roleCode): User
    {
        $user = User::factory()->create(['rol' => $roleCode]);
        self::assign($user, $roleCode);

        return $user;
    }
}
