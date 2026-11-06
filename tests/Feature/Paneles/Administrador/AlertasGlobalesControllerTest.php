<?php

use App\Models\Comunicacion\Notificacion;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\SpanishAccessFixture;

/**
 * Ejecuta la operación prepararEsquemaNotificacionesParaPrueba definida por la clase AlertasGlobalesControllerTest.
 *
 * @return void Resultado de la operación.
 */
function prepararEsquemaNotificacionesParaPrueba(): void
{
    if (! Schema::hasTable('notificaciones')) {
        Schema::create('notificaciones', function (Blueprint $tabla): void {
            $tabla->char('id', 36)->primary();
            $tabla->string('tipo');
            $tabla->string('tipo_notificable');
            $tabla->unsignedBigInteger('notificable_id');
            $tabla->text('datos');
            $tabla->timestamp('leido_en')->nullable();
            $tabla->timestamp('creado_en')->nullable();
            $tabla->timestamp('actualizado_en')->nullable();
        });
    }

    if (! Schema::hasTable('registros_auditoria')) {
        Schema::create('registros_auditoria', function (Blueprint $tabla): void {
            $tabla->bigIncrements('id');
            $tabla->unsignedBigInteger('usuario_id')->nullable();
            $tabla->string('accion', 80);
            $tabla->string('entidad', 80);
            $tabla->unsignedBigInteger('entidad_id')->nullable();
            $tabla->longText('valores_anteriores')->nullable();
            $tabla->longText('valores_nuevos')->nullable();
            $tabla->string('direccion_ip', 45)->nullable();
            $tabla->timestamp('creado_en')->useCurrent();
        });
    }
}

/**
 * Registra la información recibida y coordina su persistencia.
 *
 * @param  mixed  $destinatario  Parámetro recibido por la operación.
 * @param  mixed  $mensaje  Parámetro recibido por la operación.
 * @param  mixed  $leidaEn  Parámetro recibido por la operación.
 * @param  mixed  $tipo  Parámetro recibido por la operación.
 * @return Notificacion Resultado de la operación.
 */
function crearNotificacionParaPrueba(User $destinatario, string $mensaje, ?string $leidaEn = null, string $tipo = 'App\\Notifications\\AvisoSistema'): Notificacion
{
    return Notificacion::factory()->create([
        'tipo' => $tipo,
        'tipo_notificable' => $destinatario->getMorphClass(),
        'notificable_id' => $destinatario->getKey(),
        'datos' => ['mensaje' => $mensaje],
        'leido_en' => $leidaEn,
    ]);
}

it('muestra contadores en cero y una tabla vacía cuando el administrador no tiene notificaciones', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $administrador = SpanishAccessFixture::user('administrador');

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.dashboard.alerts'));

    $respuesta->assertOk()
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 0 && $resumen['no_leidas'] === 0)
        ->assertSee('No hay notificaciones registradas.')
        ->assertDontSee('iPhone 15')
        ->assertDontSee('Ejemplos de alertas recientes');
});

it('filtra por lectura y búsqueda y escapa el contenido recibido desde la base', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $administrador = SpanishAccessFixture::user('administrador');
    crearNotificacionParaPrueba($administrador, 'Aviso pedido de prueba <script>alert(1)</script>');
    crearNotificacionParaPrueba($administrador, 'Aviso de inventario de prueba', now()->toDateTimeString());

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.dashboard.alerts', [
        'estado' => 'no_leidas',
        'buscar' => 'pedido',
    ]));

    $respuesta->assertOk()
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 2 && $resumen['no_leidas'] === 1)
        ->assertViewHas('notificaciones', fn ($notificaciones): bool => $notificaciones->total() === 1)
        ->assertSee('Aviso pedido de prueba')
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('Aviso de inventario de prueba')
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('consulta la misma tabla real desde el buzón personal', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $administrador = SpanishAccessFixture::user('administrador');
    crearNotificacionParaPrueba($administrador, 'Aviso del buzón personal de prueba');

    $this->withoutVite()->actingAs($administrador)
        ->get(route('management.account.notifications'))
        ->assertOk()
        ->assertViewHas('notificaciones', fn ($notificaciones): bool => $notificaciones->total() === 1)
        ->assertSee('Aviso del buzón personal de prueba');
});

it('pagina las notificaciones consultadas desde el destinatario autenticado', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $administrador = SpanishAccessFixture::user('administrador');

    foreach (range(1, 21) as $numero) {
        crearNotificacionParaPrueba($administrador, 'Aviso de prueba '.$numero);
    }

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.dashboard.alerts'));

    $respuesta->assertOk()
        ->assertViewHas('notificaciones', fn ($notificaciones): bool => $notificaciones->total() === 21 && $notificaciones->count() === 20)
        ->assertSee('page=2');
});

it('marca la notificación propia como leída una sola vez y registra auditoría', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $administrador = SpanishAccessFixture::user('administrador');
    $notificacion = crearNotificacionParaPrueba($administrador, 'Aviso de lectura de prueba');

    $this->actingAs($administrador)
        ->patch(route('management.account.notifications.read', $notificacion->getKey()))
        ->assertRedirect()
        ->assertSessionHas('status', 'Notificación marcada como leída.');

    expect(DB::table('notificaciones')->where('id', $notificacion->getKey())->whereNotNull('leido_en')->exists())->toBeTrue()
        ->and(DB::table('registros_auditoria')->where('accion', 'marcar_notificacion_leida')->where('entidad', 'notificaciones')->count())->toBe(1);

    $this->actingAs($administrador)
        ->patch(route('management.account.notifications.read', $notificacion->getKey()))
        ->assertRedirect()
        ->assertSessionHas('status', 'La notificación ya estaba leída.');

    expect(DB::table('registros_auditoria')->where('accion', 'marcar_notificacion_leida')->where('entidad', 'notificaciones')->count())->toBe(1);
});

it('oculta y protege las notificaciones de otras cuentas', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $administrador = SpanishAccessFixture::user('administrador');
    $otroUsuario = User::factory()->create(['rol' => 'cliente_minorista']);
    $notificacionAjena = crearNotificacionParaPrueba($otroUsuario, 'Aviso privado de otra cuenta');

    $this->withoutVite()->actingAs($administrador)
        ->get(route('management.dashboard.alerts', ['notificacion' => $notificacionAjena->getKey()]))
        ->assertNotFound();

    $this->actingAs($administrador)
        ->patch(route('management.account.notifications.read', $notificacionAjena->getKey()))
        ->assertNotFound();

    expect(DB::table('notificaciones')->where('id', $notificacionAjena->getKey())->whereNull('leido_en')->exists())->toBeTrue()
        ->and(DB::table('registros_auditoria')->where('accion', 'marcar_notificacion_leida')->count())->toBe(0);
});

it('deniega el panel administrativo a quien no tiene el rol administrador', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $usuarioMarketing = SpanishAccessFixture::user('marketing');

    $this->actingAs($usuarioMarketing)
        ->get(route('management.dashboard.alerts'))
        ->assertForbidden();
});

it('rechaza estados de notificación que no pertenezcan a los filtros disponibles', function (): void {
    prepararEsquemaNotificacionesParaPrueba();
    $administrador = SpanishAccessFixture::user('administrador');

    $this->actingAs($administrador)
        ->get(route('management.dashboard.alerts', ['estado' => 'pendientes']))
        ->assertRedirect()
        ->assertSessionHasErrors('estado');
});
