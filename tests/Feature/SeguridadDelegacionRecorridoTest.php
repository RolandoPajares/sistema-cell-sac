<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('completa el recorrido real de rol, matriz, usuario, acceso y revocacion', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $this->withoutVite();

    $this->actingAs($administrador)
        ->post(route('management.roles_permissions.store'), [
            'codigo' => 'operador_comercial',
            'nombre' => 'Operador comercial',
            'descripcion' => 'Consulta comercial autorizada.',
            'alcance_permitido' => 'sede',
        ])
        ->assertRedirect();

    $roleId = (int) DB::table('roles')->where('codigo', 'operador_comercial')->value('id');
    $permission = DB::table('permisos')->where('codigo', 'gestion.clientes.consultar')->first(['id', 'nombre']);
    $screenPermission = DB::table('permisos')->where('codigo', 'pantalla.admin.customers.consultar')->first(['id', 'nombre']);

    expect($roleId)->toBeGreaterThan(0)
        ->and($permission)->not->toBeNull()
        ->and($screenPermission)->not->toBeNull();

    $this->actingAs($administrador)
        ->post(route('management.roles_permissions.store'), [
            'codigo' => 'otro_operador',
            'nombre' => 'Operador comercial',
            'alcance_permitido' => 'sede',
        ])
        ->assertSessionHasErrors('nombre');

    $this->actingAs($administrador)
        ->post(route('management.roles_permissions.store'), [
            'codigo' => 'segundo_operador',
            'nombre' => 'Segundo operador',
            'alcance_permitido' => 'sede',
        ])
        ->assertRedirect();

    $secondRoleId = (int) DB::table('roles')->where('codigo', 'segundo_operador')->value('id');
    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.update', $secondRoleId), [
            'nombre' => 'Operador comercial',
            'descripcion' => 'Nombre duplicado.',
            'alcance_permitido' => 'sede',
        ])
        ->assertSessionHasErrors('nombre');

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.permissions', $roleId), [
            'alcance' => 'sede',
            'permisos' => [$permission->id, $screenPermission->id],
        ])
        ->assertRedirect();

    $this->actingAs($administrador)
        ->get(route('management.roles_permissions'))
        ->assertOk()
        ->assertSee('Operador comercial')
        ->assertSee($permission->nombre);

    $this->actingAs($administrador)
        ->post(route('management.users.store'), [
            'nombre' => 'Usuario operador',
            'correo' => 'operador.recorrido@example.test',
            'rol' => 'operador_comercial',
            'password' => 'OperadorSeguro123',
            'password_confirmation' => 'OperadorSeguro123',
        ])
        ->assertRedirect();

    $operator = User::query()->where('correo', 'operador.recorrido@example.test')->firstOrFail();
    $roleAssignment = DB::table('usuarios_sedes_roles')
        ->where('usuario_id', $operator->getKey())
        ->where('rol_id', $roleId)
        ->first();

    expect($roleAssignment)->not->toBeNull()
        ->and((bool) $roleAssignment->activo)->toBeTrue();

    $this->actingAs($operator)
        ->get(route('management.customers'))
        ->assertOk();

    $this->actingAs($operator)
        ->get(route('management.roles_permissions'))
        ->assertForbidden();

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.permissions', $roleId), [
            'alcance' => 'sede',
        ])
        ->assertRedirect();

    $this->actingAs($operator)
        ->get(route('management.customers'))
        ->assertForbidden();

    expect(DB::table('roles_permisos')->where('rol_id', $roleId)->count())->toBe(0)
        ->and(DB::table('registros_auditoria')->where('accion', 'actualizar_permisos_rol')->count())->toBeGreaterThan(0)
        ->and(DB::table('registros_auditoria')->where('accion', 'user.created')->where('entidad_id', $operator->getKey())->exists())->toBeTrue();
});
