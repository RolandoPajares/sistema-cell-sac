<?php

use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioResponsablesOrganizacionales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\SpanishAccessFixture;

it('cierra la vigencia anterior y conserva el relevo al reasignar recurso y cargo', function (): void {
    Schema::create('responsables_organizacionales', function (Blueprint $tabla): void {
        $tabla->bigIncrements('id');
        $tabla->unsignedBigInteger('empresa_id');
        $tabla->unsignedBigInteger('sede_id');
        $tabla->string('tipo_recurso');
        $tabla->unsignedBigInteger('almacen_id')->nullable();
        $tabla->unsignedBigInteger('responsable_usuario_id');
        $tabla->string('cargo', 100);
        $tabla->date('vigente_desde');
        $tabla->date('vigente_hasta')->nullable();
        $tabla->string('motivo', 500);
        $tabla->unsignedBigInteger('reemplaza_asignacion_id')->nullable();
        $tabla->unsignedBigInteger('asignado_por_usuario_id')->nullable();
        $tabla->boolean('activo')->default(true);
        $tabla->timestamp('creado_en')->nullable();
        $tabla->timestamp('actualizado_en')->nullable();
    });

    $administrador = SpanishAccessFixture::user('administrador');
    $primerResponsable = SpanishAccessFixture::user('marketing');
    $segundoResponsable = SpanishAccessFixture::user('ventas_minoristas');
    $sedeId = (int) DB::table('sedes')->value('id');
    $datosBase = [
        'sede_id' => $sedeId, 'tipo_recurso' => 'sede', 'responsable_usuario_id' => $primerResponsable->id,
        'cargo' => 'Responsable operativo', 'vigente_desde' => '2026-02-01', 'vigente_hasta' => null,
        'motivo' => 'Designación inicial del cargo.',
    ];
    $servicio = app(ServicioResponsablesOrganizacionales::class);
    $primero = $servicio->reasignar($administrador, $datosBase, app(RegistradorAuditoria::class), '127.0.0.1');

    $segundo = $servicio->reasignar($administrador, array_replace($datosBase, [
        'responsable_usuario_id' => $segundoResponsable->id,
        'vigente_desde' => '2026-04-01',
        'motivo' => 'Reasignación aprobada del cargo.',
    ]), app(RegistradorAuditoria::class), '127.0.0.1');

    expect($primero->fresh()->vigente_hasta->toDateString())->toBe('2026-03-31')
        ->and($segundo->reemplaza_asignacion_id)->toBe($primero->id)
        ->and(DB::table('responsables_organizacionales')->count())->toBe(2)
        ->and(DB::table('registros_auditoria')->where('accion', 'reasignar_responsable_organizacional')->count())->toBe(2);
});
