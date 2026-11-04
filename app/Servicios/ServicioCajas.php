<?php

namespace App\Servicios;

use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Finanzas\Caja;
use App\Models\Finanzas\MovimientoCaja;
use App\Models\Finanzas\SesionCaja;
use App\Models\Organizacion\Sede;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServicioCajas
{
    public function __construct(
        private readonly ServicioAutorizacion $authorization,
        private readonly RegistradorAuditoria $audit,
    ) {}

    /** @param array{sede_id:int,codigo:string,nombre:string} $data */
    public function crear(array $data, User $actor, ?string $ip): Caja
    {
        $this->assertAdministrator($actor, 'gestion.pagos.crear');

        return DB::transaction(function () use ($data, $actor, $ip): Caja {
            $site = Sede::query()->whereKey($data['sede_id'])->where('activa', true)->lockForUpdate()->firstOrFail();
            $this->assertSite($actor, $site, 'gestion.pagos.crear');
            $code = Str::upper(trim($data['codigo']));
            $name = trim($data['nombre']);
            abort_unless($code !== '' && $name !== '', 422, 'El código y nombre de la caja son obligatorios.');
            abort_if(Caja::query()->where('sede_id', $site->id)->where('codigo', $code)->lockForUpdate()->exists(),
                422, 'Ya existe una caja con ese código en la sede.');
            $box = Caja::query()->create([
                'sede_id' => $site->id, 'codigo' => $code, 'nombre' => $name,
                'activa' => true, 'creado_en' => now(),
            ]);
            $this->audit->record((int) $actor->getKey(), 'caja.creada', 'cajas', (int) $box->getKey(),
                null, ['sede_id' => $site->id, 'codigo' => $code, 'nombre' => $name, 'activa' => true],
                $ip, scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id]);

            return $box;
        }, 3);
    }

    /** @param array{sede_id:int,codigo:string,nombre:string} $data */
    public function actualizar(Caja $box, array $data, User $actor, ?string $ip): Caja
    {
        $this->assertAdministrator($actor, 'gestion.pagos.actualizar');

        return DB::transaction(function () use ($box, $data, $actor, $ip): Caja {
            $locked = Caja::query()->whereKey($box->getKey())->lockForUpdate()->firstOrFail();
            $site = Sede::query()->whereKey($locked->sede_id)->lockForUpdate()->firstOrFail();
            $this->assertSite($actor, $site, 'gestion.pagos.actualizar');
            abort_unless((int) $data['sede_id'] === (int) $locked->sede_id, 422, 'La sede de una caja existente no puede cambiarse.');
            $code = Str::upper(trim($data['codigo']));
            $name = trim($data['nombre']);
            abort_unless($code !== '' && $name !== '', 422, 'El código y nombre de la caja son obligatorios.');
            if ($locked->codigo === $code && $locked->nombre === $name) {
                return $locked;
            }
            abort_if($this->hasOpenSession($locked), 409, 'Cierra la sesión de caja antes de editar sus datos.');
            abort_if(Caja::query()->where('sede_id', $site->id)->where('codigo', $code)
                ->whereKeyNot($locked->getKey())->lockForUpdate()->exists(), 422, 'Ya existe una caja con ese código en la sede.');
            $before = $locked->only(['codigo', 'nombre']);
            $locked->update(['codigo' => $code, 'nombre' => $name]);
            $this->audit->record((int) $actor->getKey(), 'caja.actualizada', 'cajas', (int) $locked->getKey(),
                $before, $locked->only(['codigo', 'nombre']), $ip,
                scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id]);

            return $locked;
        }, 3);
    }

    public function cambiarEstado(Caja $box, bool $active, User $actor, ?string $ip): Caja
    {
        $this->assertAdministrator($actor, 'gestion.pagos.actualizar');

        return DB::transaction(function () use ($box, $active, $actor, $ip): Caja {
            $locked = Caja::query()->whereKey($box->getKey())->lockForUpdate()->firstOrFail();
            $site = Sede::query()->whereKey($locked->sede_id)->lockForUpdate()->firstOrFail();
            $this->assertSite($actor, $site, 'gestion.pagos.actualizar');
            if ($locked->activa === $active) {
                return $locked;
            }
            abort_if(! $active && $this->hasOpenSession($locked), 409, 'Cierra la sesión de caja antes de desactivarla.');
            $locked->update(['activa' => $active]);
            $this->audit->record((int) $actor->getKey(), $active ? 'caja.activada' : 'caja.desactivada',
                'cajas', (int) $locked->getKey(), ['activa' => ! $active], ['activa' => $active], $ip,
                scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id]);

            return $locked;
        }, 3);
    }

    public function abrirSesion(Caja $box, string $initialBalance, string $token, User $actor, ?string $ip): SesionCaja
    {
        $this->assertAdministrator($actor, 'gestion.pagos.crear');
        $balance = $this->amount($initialBalance, false);

        return DB::transaction(function () use ($box, $balance, $token, $actor, $ip): SesionCaja {
            $this->lockCompany();
            $lockedBox = Caja::query()->whereKey($box->getKey())->lockForUpdate()->firstOrFail();
            $site = Sede::query()->whereKey($lockedBox->sede_id)->firstOrFail();
            $this->assertSite($actor, $site, 'gestion.pagos.crear');
            $existingId = $this->eventEntityId($token, 'sesiones_caja', 'caja.abierta');
            if ($existingId !== null) {
                $existing = SesionCaja::query()->findOrFail($existingId);
                abort_unless((int) $existing->caja_id === (int) $lockedBox->id
                    && $existing->saldo_inicial === $balance
                    && (int) $existing->usuario_apertura_id === (int) $actor->id, 409,
                    'El identificador de operación ya pertenece a otra apertura.');

                return $existing;
            }

            abort_unless($lockedBox->activa, 409, 'Activa la caja antes de abrir una sesión.');
            abort_if($this->hasOpenSession($lockedBox), 409, 'La caja ya tiene una sesión abierta.');
            $session = SesionCaja::query()->create([
                'caja_id' => $lockedBox->id, 'usuario_apertura_id' => $actor->id,
                'estado' => 'abierta', 'saldo_inicial' => $balance, 'abierta_en' => now(),
            ]);
            $this->audit->record((int) $actor->id, 'caja.abierta', 'sesiones_caja', (int) $session->id,
                null, ['caja_id' => $lockedBox->id, 'saldo_inicial' => $balance], $ip,
                scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id], eventId: $token);

            return $session;
        }, 3);
    }

    public function registrarMovimiento(
        SesionCaja $session,
        string $direction,
        string $amount,
        string $description,
        string $token,
        User $actor,
        ?string $ip,
    ): MovimientoCaja {
        $this->assertAdministrator($actor, 'gestion.pagos.crear');
        abort_unless(in_array($direction, ['entrada', 'salida'], true), 422, 'Selecciona una dirección válida.');
        $normalizedAmount = $this->amount($amount, true);
        $description = trim($description);
        abort_unless($description !== '' && mb_strlen($description) <= 500, 422, 'Describe el motivo del movimiento.');

        return DB::transaction(function () use ($session, $direction, $normalizedAmount, $description, $token, $actor, $ip): MovimientoCaja {
            $this->lockCompany();
            $lockedSession = SesionCaja::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $box = Caja::query()->whereKey($lockedSession->caja_id)->firstOrFail();
            $site = Sede::query()->whereKey($box->sede_id)->firstOrFail();
            $this->assertSite($actor, $site, 'gestion.pagos.crear');
            $existingId = $this->eventEntityId($token, 'movimientos_caja', 'caja.movimiento_manual');
            if ($existingId !== null) {
                $existing = MovimientoCaja::query()->findOrFail($existingId);
                abort_unless((int) $existing->sesion_caja_id === (int) $lockedSession->id
                    && $existing->direccion === $direction && $existing->importe === $normalizedAmount
                    && $existing->descripcion === $description && (int) $existing->creado_por_usuario_id === (int) $actor->id,
                    409, 'El identificador de operación ya pertenece a otro movimiento.');

                return $existing;
            }

            abort_unless($lockedSession->estado === 'abierta' && $box->activa, 409,
                'La caja debe estar activa y la sesión abierta para registrar movimientos.');
            if ($direction === 'salida') {
                abort_if(bccomp($this->arqueo($lockedSession)['saldo_esperado'], $normalizedAmount, 2) < 0,
                    409, 'La salida supera el efectivo esperado en caja.');
            }
            $movement = MovimientoCaja::query()->create([
                'sesion_caja_id' => $lockedSession->id, 'tipo' => 'ajuste_manual',
                'direccion' => $direction, 'importe' => $normalizedAmount,
                'descripcion' => $description, 'creado_por_usuario_id' => $actor->id, 'creado_en' => now(),
            ]);
            $this->audit->record((int) $actor->id, 'caja.movimiento_manual', 'movimientos_caja', (int) $movement->id,
                null, ['sesion_caja_id' => $lockedSession->id, 'direccion' => $direction,
                    'importe' => $normalizedAmount, 'descripcion' => $description], $ip,
                scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id], eventId: $token);

            return $movement;
        }, 3);
    }

    /** @return array{entradas:string,salidas:string,saldo_esperado:string,diferencia:?string} */
    public function arqueo(SesionCaja $session): array
    {
        $totals = MovimientoCaja::query()->where('sesion_caja_id', $session->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN direccion = 'entrada' THEN importe ELSE 0 END), 0) AS entradas")
            ->selectRaw("COALESCE(SUM(CASE WHEN direccion = 'salida' THEN importe ELSE 0 END), 0) AS salidas")
            ->first();
        $income = bcadd((string) ($totals?->entradas ?? '0'), '0', 2);
        $expense = bcadd((string) ($totals?->salidas ?? '0'), '0', 2);
        $expected = bcsub(bcadd($session->saldo_inicial, $income, 2), $expense, 2);

        return [
            'entradas' => $income,
            'salidas' => $expense,
            'saldo_esperado' => $expected,
            'diferencia' => $session->saldo_cierre_declarado === null
                ? null : bcsub($session->saldo_cierre_declarado, $expected, 2),
        ];
    }

    public function cerrarSesion(SesionCaja $session, string $declaredBalance, string $token, User $actor, ?string $ip): SesionCaja
    {
        $this->assertAdministrator($actor, 'gestion.pagos.actualizar');
        $declared = $this->amount($declaredBalance, false);

        return DB::transaction(function () use ($session, $declared, $token, $actor, $ip): SesionCaja {
            $this->lockCompany();
            $locked = SesionCaja::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $box = Caja::query()->whereKey($locked->caja_id)->firstOrFail();
            $site = Sede::query()->whereKey($box->sede_id)->firstOrFail();
            $this->assertSite($actor, $site, 'gestion.pagos.actualizar');
            $existingId = $this->eventEntityId($token, 'sesiones_caja', 'caja.cerrada');
            if ($existingId !== null) {
                abort_unless($existingId === (int) $locked->id && $locked->saldo_cierre_declarado === $declared,
                    409, 'El identificador de operación ya pertenece a otro cierre.');

                return $locked;
            }

            abort_unless($locked->estado === 'abierta', 409, 'La sesión ya está cerrada.');
            $balance = $this->arqueo($locked);
            abort_unless($balance['saldo_esperado'] === $declared, 409,
                'El arqueo presenta una diferencia de S/ '.bcsub($declared, $balance['saldo_esperado'], 2).
                '. Documenta y concilia la diferencia antes de cerrar.');
            $locked->update([
                'estado' => 'cerrada', 'saldo_cierre_declarado' => $declared,
                'usuario_cierre_id' => $actor->id, 'cerrada_en' => now(),
            ]);
            $this->audit->record((int) $actor->id, 'caja.cerrada', 'sesiones_caja', (int) $locked->id,
                ['estado' => 'abierta'], ['estado' => 'cerrada', 'saldo_cierre_declarado' => $declared,
                    'saldo_esperado' => $balance['saldo_esperado']], $ip,
                scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id], eventId: $token);

            return $locked;
        }, 3);
    }

    private function lockCompany(): void
    {
        $companyId = $this->authorization->activeCompanyId();
        abort_unless($companyId !== null, 409, 'Selecciona una única empresa activa.');
        DB::table('empresas')->where('id', $companyId)->lockForUpdate()->first();
    }

    private function eventEntityId(string $token, string $entity, string $action): ?int
    {
        abort_unless(Str::isUuid($token), 422,
            'El identificador de operación no es válido.');
        $event = RegistroAuditoria::query()->where('identificador_universal_evento', $token)
            ->first(['entidad', 'accion', 'entidad_id']);
        if ($event === null) {
            return null;
        }
        abort_unless($event->entidad === $entity && $event->accion === $action && $event->entidad_id !== null,
            409, 'El identificador de operación ya fue utilizado.');

        return (int) $event->entidad_id;
    }

    private function amount(string $value, bool $positive): string
    {
        abort_unless(preg_match('/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/', $value) === 1,
            422, 'Ingresa un importe válido con hasta dos decimales.');
        $amount = bcadd($value, '0', 2);
        abort_if($positive && bccomp($amount, '0', 2) <= 0, 422, 'El importe debe ser mayor que cero.');

        return $amount;
    }

    private function assertAdministrator(User $actor, string $permission): void
    {
        abort_unless($this->authorization->hasRole($actor, 'administrador'), 403);
        $this->authorization->assertAllowed($actor, $permission);
    }

    private function assertSite(User $actor, Sede $site, string $permission): void
    {
        abort_unless($site->activa && (int) $site->empresa_id === $this->authorization->activeCompanyId()
            && $this->authorization->allowsRecord($actor, $permission, (int) $site->empresa_id, (int) $site->id), 403);
    }

    private function hasOpenSession(Caja $box): bool
    {
        return DB::table('sesiones_caja')->where('caja_id', $box->getKey())->where('estado', 'abierta')->exists();
    }
}
