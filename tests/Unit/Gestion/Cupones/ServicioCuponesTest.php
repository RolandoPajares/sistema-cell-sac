<?php

use App\Models\Comercio\Cupon;
use App\Models\User;
use App\Servicios\Gestion\EcommerceMarketing\PromocionesCupones\ServicioCupones;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Schema::dropIfExists('registros_auditoria');
    Schema::dropIfExists('cupones');
    Schema::create('cupones', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->string('codigo', 50)->unique();
        $table->string('tipo_descuento', 20);
        $table->decimal('valor', 10, 2);
        $table->decimal('minimo_compra', 10, 2)->default(0);
        $table->unsignedInteger('limite_usos')->nullable();
        $table->unsignedInteger('usos')->default(0);
        $table->dateTime('inicia_en');
        $table->dateTime('finaliza_en');
        $table->boolean('activo')->default(true);
        $table->dateTime('creado_en')->nullable();
        $table->dateTime('actualizado_en')->nullable();
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('accion', 80);
        $table->string('entidad', 80);
        $table->unsignedBigInteger('entidad_id')->nullable();
        $table->json('valores_anteriores')->nullable();
        $table->json('valores_nuevos')->nullable();
        $table->string('direccion_ip', 45)->nullable();
        $table->timestamp('creado_en')->useCurrent();
        $table->uuid('identificador_universal_evento')->nullable()->unique();
        $table->string('rol_codigo', 40)->nullable();
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedBigInteger('sede_id')->nullable();
        $table->string('modulo', 80)->nullable();
        $table->string('resultado', 20)->nullable();
        $table->text('agente_usuario')->nullable();
        $table->string('correlacion_id', 64)->nullable();
        $table->text('observaciones')->nullable();
        $table->dateTime('ocurrido_en')->nullable();
        $table->string('tabla_afectada', 100)->nullable();
        $table->string('identificador_entidad', 128)->nullable();
        $table->json('cambios')->nullable();
        $table->string('origen', 20)->default('laravel');
    });

    createCouponSyntheticAuthorization();
});

/**
 * Registra la información recibida y coordina su persistencia.
 *
 * @return void Resultado de la operación.
 */
function createCouponSyntheticAuthorization(): void
{
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->boolean('activo');
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->boolean('activa');
    });
    Schema::create('modulos', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitado');
    });
    Schema::create('pantallas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitada');
    });
    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->string('alcance_permitido');
        $table->boolean('activo');
    });
    Schema::create('permisos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('modulo_id');
        $table->unsignedBigInteger('pantalla_id')->nullable();
        $table->string('codigo');
        $table->boolean('activo');
    });
    Schema::create('roles_permisos', function (Blueprint $table): void {
        $table->unsignedBigInteger('rol_id');
        $table->unsignedBigInteger('permiso_id');
        $table->string('alcance');
    });
    Schema::create('usuarios_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('rol_id');
        $table->boolean('activo');
        $table->dateTime('vigente_hasta')->nullable();
    });
    Schema::create('usuarios_sedes_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('rol_id');
        $table->boolean('activo');
        $table->dateTime('vigente_hasta')->nullable();
    });
    Schema::create('menus', function (Blueprint $table): void {
        $table->id();
    });
    Schema::create('roles_menus', function (Blueprint $table): void {
        $table->id();
    });
    Schema::create('elementos_menu', function (Blueprint $table): void {
        $table->id();
    });
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'SINTETICA', 'activo' => true]);
    DB::table('modulos')->insert(['id' => 1, 'habilitado' => true]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'alcance_permitido' => 'empresa', 'activo' => true]);
    $codes = ['gestion.cupones.crear', 'gestion.cupones.actualizar', 'gestion.cupones.eliminar'];
    foreach ($codes as $index => $code) {
        DB::table('permisos')->insert(['id' => $index + 1, 'modulo_id' => 1, 'codigo' => $code, 'activo' => true]);
        DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => $index + 1, 'alcance' => 'empresa']);
    }
    DB::table('usuarios_roles')->insert(['usuario_id' => 21, 'rol_id' => 1, 'activo' => true]);
}

/**
 * Ejecuta la operación makeCouponForServiceTest definida por la clase ServicioCuponesTest.
 *
 * @param  mixed  $attributes  Parámetro recibido por la operación.
 * @return Cupon Resultado de la operación.
 */
function makeCouponForServiceTest(array $attributes = []): Cupon
{
    return Cupon::query()->create($attributes + [
        'empresa_id' => 1,
        'codigo' => 'PRUEBA-'.fake()->unique()->numerify('#####'),
        'tipo_descuento' => 'porcentaje',
        'valor' => 10,
        'minimo_compra' => 0,
        'limite_usos' => null,
        'usos' => 0,
        'inicia_en' => now()->subDay(),
        'finaliza_en' => now()->addDays(30),
        'activo' => true,
        'creado_en' => now(),
    ]);
}

/**
 * Ejecuta la operación couponTestActor definida por la clase ServicioCuponesTest.
 *
 * @return User Resultado de la operación.
 */
function couponTestActor(): User
{
    $actor = new User;
    $actor->setAttribute('id', 21);
    $actor->setAttribute('rol', 'administrador');

    return $actor;
}

it('filters persisted coupons using code, discount type and current validity', function (): void {
    $matchingCoupon = makeCouponForServiceTest(['codigo' => 'VERANO10']);
    makeCouponForServiceTest(['codigo' => 'VERANO-FIJO', 'tipo_descuento' => 'fijo']);
    makeCouponForServiceTest(['codigo' => 'VERANO-FUTURO', 'inicia_en' => now()->addDay()]);

    $result = app(ServicioCupones::class)->query(['buscar' => 'VERANO', 'tipo' => 'porcentaje', 'estado' => 'Activo'], 1)->get();

    expect($result)->toHaveCount(1)->and($result->sole()->is($matchingCoupon))->toBeTrue();
});

it('registers a normalized coupon and writes one audit event', function (): void {
    $coupon = app(ServicioCupones::class)->register([
        'codigo' => 'verano-15', 'tipo_descuento' => 'porcentaje', 'valor' => '15.00', 'minimo_compra' => '50.00',
        'limite_usos' => 25, 'inicia_en' => now(), 'finaliza_en' => now()->addDays(10),
    ], couponTestActor(), '127.0.0.1');

    expect($coupon->codigo)->toBe('VERANO-15')->and((int) $coupon->usos)->toBe(0)
        ->and(DB::table('registros_auditoria')->where('accion', 'coupon.created')->count())->toBe(1);
});

it('rejects invalid percentage values and usage limits below existing redemptions', function (): void {
    $service = app(ServicioCupones::class);
    expect(fn () => $service->register([
        'codigo' => 'PORCENTAJE-INVALIDO', 'tipo_descuento' => 'porcentaje', 'valor' => 100.01,
        'minimo_compra' => 0, 'limite_usos' => null, 'inicia_en' => now(), 'finaliza_en' => now()->addDays(2),
    ], couponTestActor(), null))->toThrow(HttpException::class);

    $coupon = makeCouponForServiceTest(['codigo' => 'USOS-REGISTRADOS', 'usos' => 4, 'limite_usos' => 10]);
    expect(fn () => $service->update($coupon, [
        'codigo' => 'USOS-REGISTRADOS', 'tipo_descuento' => 'porcentaje', 'valor' => 10,
        'minimo_compra' => 0, 'limite_usos' => 3, 'inicia_en' => now(), 'finaliza_en' => now()->addDays(5),
    ], couponTestActor(), null))->toThrow(HttpException::class);
    expect((int) $coupon->fresh()->limite_usos)->toBe(10);
});

it('updates and deactivates coupons without deleting their canje history', function (): void {
    $coupon = makeCouponForServiceTest(['codigo' => 'CAMBIO10', 'usos' => 2, 'limite_usos' => 10]);
    $service = app(ServicioCupones::class);
    $service->update($coupon, [
        'codigo' => 'CAMBIO10', 'tipo_descuento' => 'fijo', 'valor' => 25, 'minimo_compra' => 100,
        'limite_usos' => 5, 'inicia_en' => now()->subDay(), 'finaliza_en' => now()->addDays(15),
    ], couponTestActor(), null);
    $service->deactivate($coupon->fresh(), couponTestActor(), null);

    expect(Cupon::query()->whereKey($coupon->id)->value('activo'))->toBe(false)
        ->and((int) Cupon::query()->whereKey($coupon->id)->value('usos'))->toBe(2)
        ->and(DB::table('registros_auditoria')->whereIn('accion', ['coupon.updated', 'coupon.deactivated'])->count())->toBe(2);
});
