<?php

namespace App\Servicios\Gestion\Finanzas;

use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Finanzas\Caja;
use App\Models\Finanzas\MovimientoCaja;
use App\Models\Finanzas\SesionCaja;
use App\Models\Organizacion\Sede;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Define la responsabilidad de la clase ServicioCajas dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioCajas
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $authorization,
        private readonly RegistradorAuditoria $audit,
    ) {}

    /** @param array{sede_id:int,codigo:string,nombre:string} $data */
    public function crear(array $data, User $actor, ?string $ip): Caja
    {
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->verificarAdministrador($actor, 'gestion.pagos.crear');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        return DB::transaction(function () use ($data, $actor, $ip): Caja {
            // Prepara en $site la consulta que obtiene o modifica los datos necesarios.
            $site = Sede::query()->whereKey($data['sede_id'])->where('activa', true)->lockForUpdate()->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarSede($actor, $site, 'gestion.pagos.crear');
            // Guarda en $code el resultado necesario para continuar la operacion.
            $code = Str::upper(trim($data['codigo']));
            // Guarda en $name el resultado necesario para continuar la operacion.
            $name = trim($data['nombre']);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($code !== '' && $name !== '', 422, 'El código y nombre de la caja son obligatorios.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if(Caja::query()->where('sede_id', $site->id)->where('codigo', $code)->lockForUpdate()->exists(),
                422, 'Ya existe una caja con ese código en la sede.');
            // Prepara en $box la consulta que obtiene o modifica los datos necesarios.
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
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->verificarAdministrador($actor, 'gestion.pagos.actualizar');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($box, $data, $actor, $ip): Caja {
            // Prepara en $locked la consulta que obtiene o modifica los datos necesarios.
            $locked = Caja::query()->whereKey($box->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $site la consulta que obtiene o modifica los datos necesarios.
            $site = Sede::query()->whereKey($locked->sede_id)->lockForUpdate()->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarSede($actor, $site, 'gestion.pagos.actualizar');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless((int) $data['sede_id'] === (int) $locked->sede_id, 422, 'La sede de una caja existente no puede cambiarse.');
            // Guarda en $code el resultado necesario para continuar la operacion.
            $code = Str::upper(trim($data['codigo']));
            // Guarda en $name el resultado necesario para continuar la operacion.
            $name = trim($data['nombre']);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($code !== '' && $name !== '', 422, 'El código y nombre de la caja son obligatorios.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($locked->codigo === $code && $locked->nombre === $name) {
                return $locked;
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($this->tieneSesionAbierta($locked), 409, 'Cierra la sesión de caja antes de editar sus datos.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if(Caja::query()->where('sede_id', $site->id)->where('codigo', $code)
                ->whereKeyNot($locked->getKey())->lockForUpdate()->exists(), 422, 'Ya existe una caja con ese código en la sede.');
            // Guarda en $before el resultado necesario para continuar la operacion.
            $before = $locked->only(['codigo', 'nombre']);
            $locked->update(['codigo' => $code, 'nombre' => $name]);
            $this->audit->record((int) $actor->getKey(), 'caja.actualizada', 'cajas', (int) $locked->getKey(),
                $before, $locked->only(['codigo', 'nombre']), $ip,
                scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id]);

            return $locked;
        }, 3);
    }

    /**
     * Ejecuta la operación cambiarEstado definida por la clase ServicioCajas.
     *
     * @param  mixed  $box  Parámetro recibido por la operación.
     * @param  mixed  $active  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Caja Resultado de la operación.
     */
    public function cambiarEstado(Caja $box, bool $active, User $actor, ?string $ip): Caja
    {
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->verificarAdministrador($actor, 'gestion.pagos.actualizar');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($box, $active, $actor, $ip): Caja {
            // Prepara en $locked la consulta que obtiene o modifica los datos necesarios.
            $locked = Caja::query()->whereKey($box->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $site la consulta que obtiene o modifica los datos necesarios.
            $site = Sede::query()->whereKey($locked->sede_id)->lockForUpdate()->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarSede($actor, $site, 'gestion.pagos.actualizar');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($locked->activa === $active) {
                return $locked;
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if(! $active && $this->tieneSesionAbierta($locked), 409, 'Cierra la sesión de caja antes de desactivarla.');
            $locked->update(['activa' => $active]);
            $this->audit->record((int) $actor->getKey(), $active ? 'caja.activada' : 'caja.desactivada',
                'cajas', (int) $locked->getKey(), ['activa' => ! $active], ['activa' => $active], $ip,
                scope: ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id]);

            return $locked;
        }, 3);
    }

    /**
     * Ejecuta la operación abrirSesion definida por la clase ServicioCajas.
     *
     * @param  mixed  $box  Parámetro recibido por la operación.
     * @param  mixed  $initialBalance  Parámetro recibido por la operación.
     * @param  mixed  $token  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return SesionCaja Resultado de la operación.
     */
    public function abrirSesion(Caja $box, string $initialBalance, string $token, User $actor, ?string $ip): SesionCaja
    {
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->verificarAdministrador($actor, 'gestion.pagos.crear');
        // Guarda en $balance el resultado necesario para continuar la operacion.
        $balance = $this->importe($initialBalance, false);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($box, $balance, $token, $actor, $ip): SesionCaja {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->bloquearEmpresa();
            // Prepara en $lockedBox la consulta que obtiene o modifica los datos necesarios.
            $lockedBox = Caja::query()->whereKey($box->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $site la consulta que obtiene o modifica los datos necesarios.
            $site = Sede::query()->whereKey($lockedBox->sede_id)->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarSede($actor, $site, 'gestion.pagos.crear');
            // Guarda en $existingId el resultado necesario para continuar la operacion.
            $existingId = $this->idEntidadEvento($token, 'sesiones_caja', 'caja.abierta');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($existingId !== null) {
                // Prepara en $existing la consulta que obtiene o modifica los datos necesarios.
                $existing = SesionCaja::query()->findOrFail($existingId);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless((int) $existing->caja_id === (int) $lockedBox->id
                    && $existing->saldo_inicial === $balance
                    && (int) $existing->usuario_apertura_id === (int) $actor->id, 409,
                    'El identificador de operación ya pertenece a otra apertura.');

                return $existing;
            }

            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($lockedBox->activa, 409, 'Activa la caja antes de abrir una sesión.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($this->tieneSesionAbierta($lockedBox), 409, 'La caja ya tiene una sesión abierta.');
            // Prepara en $session la consulta que obtiene o modifica los datos necesarios.
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

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return mixed Resultado de la operación.
     */
    public function registrarMovimiento(
        SesionCaja $session,
        string $direction,
        string $amount,
        string $description,
        string $token,
        User $actor,
        ?string $ip,
    ): MovimientoCaja {
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->verificarAdministrador($actor, 'gestion.pagos.crear');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(in_array($direction, ['entrada', 'salida'], true), 422, 'Selecciona una dirección válida.');
        // Guarda en $normalizedAmount el resultado necesario para continuar la operacion.
        $normalizedAmount = $this->importe($amount, true);
        // Guarda en $description el resultado necesario para continuar la operacion.
        $description = trim($description);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($description !== '' && mb_strlen($description) <= 500, 422, 'Describe el motivo del movimiento.');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($session, $direction, $normalizedAmount, $description, $token, $actor, $ip): MovimientoCaja {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->bloquearEmpresa();
            // Prepara en $lockedSession la consulta que obtiene o modifica los datos necesarios.
            $lockedSession = SesionCaja::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $box la consulta que obtiene o modifica los datos necesarios.
            $box = Caja::query()->whereKey($lockedSession->caja_id)->firstOrFail();
            // Prepara en $site la consulta que obtiene o modifica los datos necesarios.
            $site = Sede::query()->whereKey($box->sede_id)->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarSede($actor, $site, 'gestion.pagos.crear');
            // Guarda en $existingId el resultado necesario para continuar la operacion.
            $existingId = $this->idEntidadEvento($token, 'movimientos_caja', 'caja.movimiento_manual');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($existingId !== null) {
                // Prepara en $existing la consulta que obtiene o modifica los datos necesarios.
                $existing = MovimientoCaja::query()->findOrFail($existingId);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless((int) $existing->sesion_caja_id === (int) $lockedSession->id
                    && $existing->direccion === $direction && $existing->importe === $normalizedAmount
                    && $existing->descripcion === $description && (int) $existing->creado_por_usuario_id === (int) $actor->id,
                    409, 'El identificador de operación ya pertenece a otro movimiento.');

                return $existing;
            }

            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($lockedSession->estado === 'abierta' && $box->activa, 409,
                'La caja debe estar activa y la sesión abierta para registrar movimientos.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($direction === 'salida') {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(bccomp($this->arqueo($lockedSession)['saldo_esperado'], $normalizedAmount, 2) < 0,
                    409, 'La salida supera el efectivo esperado en caja.');
            }
            // Prepara en $movement la consulta que obtiene o modifica los datos necesarios.
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
        // Prepara en $totals la consulta que obtiene o modifica los datos necesarios.
        $totals = MovimientoCaja::query()->where('sesion_caja_id', $session->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN direccion = 'entrada' THEN importe ELSE 0 END), 0) AS entradas")
            ->selectRaw("COALESCE(SUM(CASE WHEN direccion = 'salida' THEN importe ELSE 0 END), 0) AS salidas")
            ->first();
        // Guarda en $income el resultado necesario para continuar la operacion.
        $income = bcadd((string) ($totals?->entradas ?? '0'), '0', 2);
        // Guarda en $expense el resultado necesario para continuar la operacion.
        $expense = bcadd((string) ($totals?->salidas ?? '0'), '0', 2);
        // Guarda en $expected el resultado necesario para continuar la operacion.
        $expected = bcsub(bcadd($session->saldo_inicial, $income, 2), $expense, 2);

        return [
            'entradas' => $income,
            'salidas' => $expense,
            'saldo_esperado' => $expected,
            'diferencia' => $session->saldo_cierre_declarado === null
                ? null : bcsub($session->saldo_cierre_declarado, $expected, 2),
        ];
    }

    /**
     * Ejecuta la operación cerrarSesion definida por la clase ServicioCajas.
     *
     * @param  mixed  $session  Parámetro recibido por la operación.
     * @param  mixed  $declaredBalance  Parámetro recibido por la operación.
     * @param  mixed  $token  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return SesionCaja Resultado de la operación.
     */
    public function cerrarSesion(SesionCaja $session, string $declaredBalance, string $token, User $actor, ?string $ip): SesionCaja
    {
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->verificarAdministrador($actor, 'gestion.pagos.actualizar');
        // Guarda en $declared el resultado necesario para continuar la operacion.
        $declared = $this->importe($declaredBalance, false);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($session, $declared, $token, $actor, $ip): SesionCaja {
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->bloquearEmpresa();
            // Prepara en $locked la consulta que obtiene o modifica los datos necesarios.
            $locked = SesionCaja::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $box la consulta que obtiene o modifica los datos necesarios.
            $box = Caja::query()->whereKey($locked->caja_id)->firstOrFail();
            // Prepara en $site la consulta que obtiene o modifica los datos necesarios.
            $site = Sede::query()->whereKey($box->sede_id)->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarSede($actor, $site, 'gestion.pagos.actualizar');
            // Guarda en $existingId el resultado necesario para continuar la operacion.
            $existingId = $this->idEntidadEvento($token, 'sesiones_caja', 'caja.cerrada');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($existingId !== null) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($existingId === (int) $locked->id && $locked->saldo_cierre_declarado === $declared,
                    409, 'El identificador de operación ya pertenece a otro cierre.');

                return $locked;
            }

            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($locked->estado === 'abierta', 409, 'La sesión ya está cerrada.');
            // Guarda en $balance el resultado necesario para continuar la operacion.
            $balance = $this->arqueo($locked);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
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

    /**
     * Ejecuta la operación lockCompany definida por la clase ServicioCajas.
     *
     * @return void Resultado de la operación.
     */
    private function bloquearEmpresa(): void
    {
        // Guarda en $companyId el resultado necesario para continuar la operacion.
        $companyId = $this->authorization->activeCompanyId();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($companyId !== null, 409, 'Selecciona una única empresa activa.');
        DB::table('empresas')->where('id', $companyId)->lockForUpdate()->first();
    }

    /**
     * Ejecuta la operación idEntidadEvento definida por la clase ServicioCajas.
     *
     * @param  mixed  $token  Parámetro recibido por la operación.
     * @param  mixed  $entity  Parámetro recibido por la operación.
     * @param  mixed  $action  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function idEntidadEvento(string $token, string $entity, string $action): ?int
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Str::isUuid($token), 422,
            'El identificador de operación no es válido.');
        // Prepara en $event la consulta que obtiene o modifica los datos necesarios.
        $event = RegistroAuditoria::query()->where('identificador_universal_evento', $token)
            ->first(['entidad', 'accion', 'entidad_id']);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($event === null) {
            return null;
        }
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($event->entidad === $entity && $event->accion === $action && $event->entidad_id !== null,
            409, 'El identificador de operación ya fue utilizado.');

        return (int) $event->entidad_id;
    }

    /**
     * Ejecuta la operación amount definida por la clase ServicioCajas.
     *
     * @param  mixed  $value  Parámetro recibido por la operación.
     * @param  mixed  $positive  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function importe(string $value, bool $positive): string
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(preg_match('/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/', $value) === 1,
            422, 'Ingresa un importe válido con hasta dos decimales.');
        // Guarda en $amount el resultado necesario para continuar la operacion.
        $amount = bcadd($value, '0', 2);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($positive && bccomp($amount, '0', 2) <= 0, 422, 'El importe debe ser mayor que cero.');

        return $amount;
    }

    /**
     * Ejecuta la operación verificarAdministrador definida por la clase ServicioCajas.
     *
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $permission  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function verificarAdministrador(User $actor, string $permission): void
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($this->authorization->allowsGlobal($actor, 'gestion.seguridad.consultar'), 403);
        $this->authorization->assertAllowed($actor, $permission);
    }

    /**
     * Ejecuta la operación verificarSede definida por la clase ServicioCajas.
     *
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $site  Parámetro recibido por la operación.
     * @param  mixed  $permission  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function verificarSede(User $actor, Sede $site, string $permission): void
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($site->activa && (int) $site->empresa_id === $this->authorization->activeCompanyId()
            && $this->authorization->allowsRecord($actor, $permission, (int) $site->empresa_id, (int) $site->id), 403);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $box  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function tieneSesionAbierta(Caja $box): bool
    {
        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return DB::table('sesiones_caja')->where('caja_id', $box->getKey())->where('estado', 'abierta')->exists();
    }
}
