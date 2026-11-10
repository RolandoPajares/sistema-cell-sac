<?php

use App\Models\Cuenta\PerfilCampo;
use App\Models\Seguridad\Rol;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('muestra y actualiza campos de perfil habilitados por el rol vigente', function (): void {
    $role = Rol::query()->create([
        'codigo' => 'perfil_dinamico',
        'nombre' => 'Perfil dinámico',
        'alcance_permitido' => 'propio',
        'activo' => true,
    ]);
    $user = User::query()->create([
        'nombre' => 'Usuario de prueba',
        'correo' => 'perfil-dinamico@example.test',
        'password' => 'ClaveSegura123',
        'rol' => $role->codigo,
        'activo' => true,
    ]);
    DB::table('usuarios_roles')->insert([
        'usuario_id' => $user->getKey(),
        'rol_id' => $role->getKey(),
        'asignado_en' => now(),
        'activo' => true,
    ]);
    $field = PerfilCampo::query()->create([
        'codigo' => 'cargo',
        'etiqueta' => 'Cargo',
        'tipo' => 'text',
        'activo' => true,
    ]);
    DB::table('roles_perfil_campos')->insert([
        'rol_id' => $role->getKey(),
        'perfil_campo_id' => $field->getKey(),
        'visible' => true,
        'editable' => true,
        'requerido' => false,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);

    $this->withoutVite()->actingAs($user)
        ->get(route('account.profile.show'))
        ->assertOk()
        ->assertSee('Información adicional')
        ->assertSee('Cargo');

    $this->actingAs($user)->put(route('account.profile'), [
        'nombre' => 'Usuario actualizado',
        'correo' => $user->correo,
        'perfil' => [$field->getKey() => 'Responsable de pruebas'],
        'rol' => 'administrador',
        'activo' => false,
    ])->assertRedirect();

    expect($user->fresh()->nombre)->toBe('Usuario actualizado')
        ->and($user->fresh()->rol)->toBe($role->codigo)
        ->and(DB::table('usuarios_perfil_campos')->where('usuario_id', $user->getKey())->where('perfil_campo_id', $field->getKey())->value('valor'))
        ->toBe('Responsable de pruebas');
});

it('elimina la fotografía propia sin tocar archivos fuera del directorio de avatares', function (): void {
    Storage::fake('public');
    $user = User::query()->create([
        'nombre' => 'Usuario con fotografía',
        'correo' => 'fotografia@example.test',
        'password' => 'ClaveSegura123',
        'activo' => true,
        'foto_perfil' => 'avatars/fotografia.jpg',
    ]);
    Storage::disk('public')->put('avatars/fotografia.jpg', 'imagen');

    $this->actingAs($user)
        ->delete(route('management.account.avatar.delete'))
        ->assertRedirect();

    expect($user->fresh()->foto_perfil)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/fotografia.jpg');
});
