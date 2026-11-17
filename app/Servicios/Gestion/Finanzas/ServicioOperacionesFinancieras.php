<?php

namespace App\Servicios\Gestion\Finanzas;

use App\Models\Comercio\DevolucionGarantia;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\Compras\Compra;
use App\Models\Finanzas\Caja;
use App\Models\Finanzas\CuentaBancaria;
use App\Models\Finanzas\MedioPago;
use App\Models\Finanzas\MovimientoCaja;
use App\Models\Finanzas\Pago;
use App\Models\Finanzas\Reembolso;
use App\Models\Finanzas\SesionCaja;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Define la responsabilidad de la clase ServicioOperacionesFinancieras dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioOperacionesFinancieras
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $authorization,
        private readonly RegistradorAuditoria $audit,
        private readonly ServicioCajas $boxes,
    ) {}

    /**
     * @param  array{token:string,origen:string,medio_pago_id:int,importe:string,referencia?:string|null,sesion_caja_id?:int|null,cuenta_bancaria_id?:int|null}  $data
     */
    public function registrarPago(array $data, User $actor, ?string $ip): Pago
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.pagos.crear';
        $this->authorization->assertAllowed($actor, $permission);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Str::isUuid($data['token']), 422, 'El identificador de la solicitud no es válido.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(preg_match('/\A(pedido|venta|compra):([1-9][0-9]*)\z/', $data['origen'], $matches) === 1, 422, 'El documento seleccionado no es válido.');
        // Guarda en $kind el resultado necesario para continuar la operacion.
        $kind = $matches[1];
        // Guarda en $documentId el resultado necesario para continuar la operacion.
        $documentId = (int) $matches[2];
        // Guarda en $amountCents el resultado necesario para continuar la operacion.
        $amountCents = $this->centimos((string) $data['importe']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($amountCents > 0, 422, 'El importe debe ser mayor que cero.');
        // Guarda en $location el resultado necesario para continuar la operacion.
        $location = $this->authorization->operationSite($actor);
        // Guarda en $companyId el resultado necesario para continuar la operacion.
        $companyId = $location['empresa_id'];
        // Guarda en $siteId el resultado necesario para continuar la operacion.
        $siteId = $location['sede_id'];
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($companyId !== null && $siteId !== null, 409, 'Selecciona una sede activa antes de registrar la solicitud.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($this->authorization->allowsRecord($actor, $permission, $companyId, $siteId), 403);
        // Guarda en $code el resultado necesario para continuar la operacion.
        $code = 'PAG-'.strtoupper($data['token']);
        // Guarda en $reference el resultado necesario para continuar la operacion.
        $reference = trim((string) ($data['referencia'] ?? '')) ?: null;
        // Guarda en $cashSessionId el resultado necesario para continuar la operacion.
        $cashSessionId = isset($data['sesion_caja_id']) ? (int) $data['sesion_caja_id'] : null;
        // Guarda en $bankAccountId el resultado necesario para continuar la operacion.
        $bankAccountId = isset($data['cuenta_bancaria_id']) ? (int) $data['cuenta_bancaria_id'] : null;
        $type = $kind === 'compra' ? 'pago' : 'cobro';

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        return DB::transaction(function () use ($data, $actor, $ip, $kind, $documentId, $amountCents, $companyId, $siteId, $code, $reference, $cashSessionId, $bankAccountId, $type): Pago {
            // Prepara en $company la consulta que obtiene o modifica los datos necesarios.
            $company = DB::table('empresas')->where('id', $companyId)->lockForUpdate()->first(['id', 'activo']);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($company !== null && $company->activo, 409, 'La empresa seleccionada no está activa.');
            // Prepara en $existing la consulta que obtiene o modifica los datos necesarios.
            $existing = Pago::query()->where('empresa_id', $companyId)->where('codigo', $code)->lockForUpdate()->first();
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($existing !== null) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless((int) $existing->usuario_id === (int) $actor->getKey()
                    && $existing->tipo === $type
                    && (int) $existing->{$kind.'_id'} === $documentId
                    && (int) $existing->medio_pago_id === (int) $data['medio_pago_id']
                    && ($existing->sesion_caja_id === null ? null : (int) $existing->sesion_caja_id) === $cashSessionId
                    && ($existing->cuenta_bancaria_id === null ? null : (int) $existing->cuenta_bancaria_id) === $bankAccountId
                    && $this->centimos((string) $existing->importe) === $amountCents
                    && $existing->referencia === $reference, 409, 'El identificador ya corresponde a otra solicitud.');

                return $existing;
            }

            // Guarda en $document el resultado necesario para continuar la operacion.
            $document = $this->documento($kind, $documentId);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless((int) $document->empresa_id === $companyId && (int) $document->sede_id === $siteId, 403);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless(! in_array(mb_strtolower((string) $document->estado), ['anulado', 'anulada', 'cancelado', 'cancelada', 'rechazada'], true),
                409, 'El estado del documento no permite registrar pagos.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($document instanceof Compra) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($document->activo, 409, 'La compra ya no está activa.');
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($document instanceof Pedido) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless(! Venta::query()->where('pedido_id', $document->getKey())->exists(),
                    409, 'El pedido ya tiene una venta asociada. Registra el cobro en la venta.');
            }
            // Guarda en $currency el resultado necesario para continuar la operacion.
            $currency = $document instanceof Pedido ? 'PEN' : (string) $document->moneda;
            // Guarda en $documentTotal el resultado necesario para continuar la operacion.
            $documentTotal = $this->centimos((string) $document->total);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($documentTotal > 0, 409, 'El documento no tiene un importe válido.');

            // Prepara en $method la consulta que obtiene o modifica los datos necesarios.
            $method = MedioPago::query()->whereKey($data['medio_pago_id'])->lockForUpdate()->first();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($method?->activo, 422, 'El medio de pago no está activo.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($method->requiere_caja && $method->requiere_banco, 409,
                'El medio de pago no puede requerir caja y banco simultáneamente.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($method->requiere_caja) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($cashSessionId !== null && $bankAccountId === null, 422,
                    'Selecciona una sesión de caja y no una cuenta bancaria para efectivo.');
                // Prepara en $cashSession la consulta que obtiene o modifica los datos necesarios.
                $cashSession = SesionCaja::query()->whereKey($cashSessionId)->lockForUpdate()->firstOrFail();
                // Prepara en $cashBox la consulta que obtiene o modifica los datos necesarios.
                $cashBox = Caja::query()->whereKey($cashSession->caja_id)->firstOrFail();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($cashSession->estado === 'abierta' && $cashBox->activa
                    && (int) $cashBox->sede_id === $siteId, 409,
                    'La sesión de caja no está abierta en la sede de la operación.');
            } elseif ($method->requiere_banco) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($bankAccountId !== null && $cashSessionId === null && $reference !== null, 422,
                    'La transferencia declarada requiere cuenta bancaria y referencia verificable.');
                // Prepara en $bankAccount la consulta que obtiene o modifica los datos necesarios.
                $bankAccount = CuentaBancaria::query()->whereKey($bankAccountId)->firstOrFail();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($bankAccount->activa && (int) $bankAccount->empresa_id === $companyId
                    && $bankAccount->moneda === $currency, 409,
                    'La cuenta bancaria no está activa para la empresa y moneda del documento.');
            } else {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($cashSessionId === null && $bankAccountId === null, 422,
                    'El medio de pago seleccionado no utiliza caja ni cuenta bancaria.');
            }

            // Prepara en $prior la consulta que obtiene o modifica los datos necesarios.
            $prior = Pago::query()->where('empresa_id', $companyId)->where('sede_id', $siteId)
                ->where('tipo', $type)->whereNotIn('estado', ['anulado', 'rechazado']);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($document instanceof Venta && $document->pedido_id !== null) {
                $prior->where(fn (Builder $query): Builder => $query
                    ->where('venta_id', $documentId)->orWhere('pedido_id', $document->pedido_id));
            } else {
                $prior->where($kind.'_id', $documentId);
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($reference !== null) {
                // Guarda en $sameReference el resultado de la consulta ejecutada.
                $sameReference = (clone $prior)->where('referencia', $reference)->lockForUpdate()->first();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($sameReference !== null, 409, 'Ya existe una solicitud para esa referencia y documento.');
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($amountCents <= $documentTotal - $this->centimos((string) $prior->sum('importe')),
                422, 'El importe supera el saldo disponible del documento.');

            // Prepara en $payment la consulta que obtiene o modifica los datos necesarios.
            $payment = Pago::query()->create([
                'empresa_id' => $companyId,
                'sede_id' => $siteId,
                'codigo' => $code,
                'tipo' => $type,
                'estado' => 'pendiente',
                'medio_pago_id' => $method->getKey(),
                'sesion_caja_id' => $cashSessionId,
                'cuenta_bancaria_id' => $bankAccountId,
                'usuario_id' => $actor->getKey(),
                $kind.'_id' => $documentId,
                'moneda' => $currency,
                'importe' => $this->importe($amountCents),
                'referencia' => $reference,
                'fecha_pago' => now(),
                'creado_en' => now(),
            ]);
            $this->audit->record((int) $actor->getKey(), 'pago.solicitado', 'pagos', (int) $payment->getKey(),
                null, ['codigo' => $code, 'estado' => 'pendiente', $kind.'_id' => $documentId, 'importe' => $payment->importe],
                $ip, scope: ['empresa_id' => $companyId, 'sede_id' => $siteId]);

            return $payment;
        }, 3);
    }

    /**
     * Ejecuta la operación confirmarPagoEfectivo definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $payment  Parámetro recibido por la operación.
     * @param  mixed  $countedAmount  Parámetro recibido por la operación.
     * @param  mixed  $observation  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Pago Resultado de la operación.
     */
    public function confirmarPagoEfectivo(Pago $payment, string $countedAmount, string $observation, User $actor, ?string $ip): Pago
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.pagos.actualizar';
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($this->authorization->allowsGlobal($actor, 'gestion.seguridad.consultar'), 403);
        $this->authorization->assertAllowed($actor, $permission);
        // Guarda en $countedCents el resultado necesario para continuar la operacion.
        $countedCents = $this->centimos($countedAmount);
        // Guarda en $observation el resultado necesario para continuar la operacion.
        $observation = trim($observation);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($countedCents > 0 && $observation !== '' && mb_strlen($observation) <= 500, 422,
            'Declara el importe contado y documenta la verificación.');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($payment, $countedCents, $observation, $actor, $ip, $permission): Pago {
            DB::table('empresas')->where('id', $this->authorization->activeCompanyId())->lockForUpdate()->first();
            // Prepara en $locked la consulta que obtiene o modifica los datos necesarios.
            $locked = Pago::query()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->authorization->allowsRecord($actor, $permission,
                (int) $locked->empresa_id, (int) $locked->sede_id), 403);
            // Prepara en $method la consulta que obtiene o modifica los datos necesarios.
            $method = MedioPago::query()->whereKey($locked->medio_pago_id)->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($method->requiere_caja && ! $method->requiere_banco && $locked->sesion_caja_id !== null,
                409, 'Este pago no corresponde a efectivo en caja.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($countedCents === $this->centimos($locked->importe), 422,
                'El efectivo contado no coincide con el importe del pago.');
            $direction = $locked->tipo === 'cobro' ? 'entrada' : 'salida';
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($locked->estado === 'registrado') {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless(MovimientoCaja::query()->where('pago_id', $locked->id)
                    ->where('sesion_caja_id', $locked->sesion_caja_id)->where('direccion', $direction)
                    ->where('importe', $locked->importe)->exists(), 409,
                    'El pago figura registrado pero no tiene movimiento de caja comprobable.');

                return $locked;
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($locked->estado === 'pendiente', 409, 'Solo se puede confirmar efectivo pendiente.');
            // Prepara en $session la consulta que obtiene o modifica los datos necesarios.
            $session = SesionCaja::query()->whereKey($locked->sesion_caja_id)->lockForUpdate()->firstOrFail();
            // Prepara en $box la consulta que obtiene o modifica los datos necesarios.
            $box = Caja::query()->whereKey($session->caja_id)->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($session->estado === 'abierta' && $box->activa
                && (int) $box->sede_id === (int) $locked->sede_id, 409,
                'La sesión de caja ya no admite operaciones.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if(MovimientoCaja::query()->where('pago_id', $locked->id)->exists(), 409,
                'El pago ya tiene un movimiento de caja.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($direction === 'salida') {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($this->centimos($this->boxes->arqueo($session)['saldo_esperado']) < $countedCents,
                    409, 'La caja no tiene efectivo suficiente para este pago.');
            }
            // Prepara en $movement la consulta que obtiene o modifica los datos necesarios.
            $movement = MovimientoCaja::query()->create([
                'sesion_caja_id' => $session->id, 'pago_id' => $locked->id,
                'tipo' => $direction === 'entrada' ? 'cobro_efectivo' : 'pago_efectivo',
                'direccion' => $direction, 'importe' => $locked->importe,
                'descripcion' => $observation, 'creado_por_usuario_id' => $actor->id, 'creado_en' => now(),
            ]);
            $locked->update(['estado' => 'registrado', 'fecha_pago' => now()]);
            $scope = ['empresa_id' => (int) $locked->empresa_id, 'sede_id' => (int) $locked->sede_id];
            $this->audit->record((int) $actor->id, 'pago.confirmado_efectivo', 'pagos', (int) $locked->id,
                ['estado' => 'pendiente'], ['estado' => 'registrado', 'movimiento_caja_id' => $movement->id],
                $ip, observations: $observation, scope: $scope);
            $this->audit->record((int) $actor->id, 'caja.movimiento_pago', 'movimientos_caja', (int) $movement->id,
                null, ['sesion_caja_id' => $session->id, 'pago_id' => $locked->id,
                    'direccion' => $direction, 'importe' => $locked->importe], $ip, scope: $scope);

            return $locked;
        }, 3);
    }

    /**
     * Ejecuta la operación anularPago definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $payment  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $reason  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Pago Resultado de la operación.
     */
    public function anularPago(Pago $payment, User $actor, string $reason, ?string $ip): Pago
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.pagos.anular';
        $this->authorization->assertAllowed($actor, $permission);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($payment, $actor, $reason, $ip, $permission): Pago {
            // Prepara en $locked la consulta que obtiene o modifica los datos necesarios.
            $locked = Pago::query()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->authorization->allowsRecord($actor, $permission, (int) $locked->empresa_id, (int) $locked->sede_id), 403);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($locked->estado === 'anulado') {
                return $locked;
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($locked->estado === 'pendiente', 409, 'Solo se pueden anular solicitudes pendientes.');
            $locked->update(['estado' => 'anulado']);
            $this->audit->record((int) $actor->getKey(), 'pago.anulado', 'pagos', (int) $locked->getKey(),
                ['estado' => 'pendiente'], ['estado' => 'anulado'], $ip, observations: $reason,
                scope: ['empresa_id' => (int) $locked->empresa_id, 'sede_id' => (int) $locked->sede_id]);

            return $locked;
        }, 3);
    }

    /** @param array{devolucion_id:int,pago_id:int,importe:string} $data */
    public function solicitarReembolso(array $data, User $actor, ?string $ip): Reembolso
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.reembolsos.crear';
        $this->authorization->assertAllowed($actor, $permission);
        // Guarda en $amountCents el resultado necesario para continuar la operacion.
        $amountCents = $this->centimos((string) $data['importe']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($amountCents > 0, 422, 'El importe debe ser mayor que cero.');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($data, $actor, $ip, $permission, $amountCents): Reembolso {
            // Prepara en $payment la consulta que obtiene o modifica los datos necesarios.
            $payment = Pago::query()->whereKey($data['pago_id'])->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->authorization->allowsRecord($actor, $permission, (int) $payment->empresa_id, (int) $payment->sede_id), 403);
            // Prepara en $existing la consulta que obtiene o modifica los datos necesarios.
            $existing = Reembolso::query()->where('pago_id', $payment->getKey())->lockForUpdate()->first();
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($existing !== null) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless((int) $existing->devolucion_id === (int) $data['devolucion_id']
                    && $this->centimos((string) $existing->importe) === $amountCents, 409,
                    'El cobro ya tiene otra solicitud de reembolso.');

                return $existing;
            }
            // Prepara en $return la consulta que obtiene o modifica los datos necesarios.
            $return = DevolucionGarantia::query()->whereKey($data['devolucion_id'])->lockForUpdate()->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarOrigenReembolso($payment, $return);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($amountCents <= $this->centimos((string) $payment->importe), 422,
                'El reembolso supera el importe del cobro original.');

            // Prepara en $refund la consulta que obtiene o modifica los datos necesarios.
            $refund = Reembolso::query()->create([
                'devolucion_id' => $return->getKey(),
                'pago_id' => $payment->getKey(),
                'importe' => $this->importe($amountCents),
                'estado' => 'pendiente',
                'creado_en' => now(),
            ]);
            $this->audit->record((int) $actor->getKey(), 'reembolso.solicitado', 'reembolsos', (int) $refund->getKey(),
                null, ['estado' => 'pendiente', 'devolucion_id' => $return->getKey(), 'pago_id' => $payment->getKey(),
                    'importe' => $refund->importe], $ip,
                scope: ['empresa_id' => (int) $payment->empresa_id, 'sede_id' => (int) $payment->sede_id]);

            return $refund;
        }, 3);
    }

    /**
     * Ejecuta la operación aprobarReembolso definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $refund  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Reembolso Resultado de la operación.
     */
    public function aprobarReembolso(Reembolso $refund, User $actor, ?string $ip): Reembolso
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.reembolsos.aprobar';
        $this->authorization->assertAllowed($actor, $permission);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($refund, $actor, $ip, $permission): Reembolso {
            // Prepara en $locked la consulta que obtiene o modifica los datos necesarios.
            $locked = Reembolso::query()->whereKey($refund->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $payment la consulta que obtiene o modifica los datos necesarios.
            $payment = Pago::query()->whereKey($locked->pago_id)->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->authorization->allowsRecord($actor, $permission, (int) $payment->empresa_id, (int) $payment->sede_id), 403);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($locked->estado === 'aprobado') {
                return $locked;
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($locked->estado === 'pendiente', 409, 'El reembolso ya no está pendiente.');
            // Prepara en $return la consulta que obtiene o modifica los datos necesarios.
            $return = DevolucionGarantia::query()->whereKey($locked->devolucion_id)->lockForUpdate()->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->verificarOrigenReembolso($payment, $return);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->tieneEvidenciaPago($payment), 409,
                'El cobro original requiere un ingreso de caja documentado antes de aprobar.');
            $locked->update(['estado' => 'aprobado', 'autorizado_por_usuario_id' => $actor->getKey()]);
            $this->audit->record((int) $actor->getKey(), 'reembolso.aprobado', 'reembolsos', (int) $locked->getKey(),
                ['estado' => 'pendiente'], ['estado' => 'aprobado', 'autorizado_por_usuario_id' => $actor->getKey()],
                $ip, scope: ['empresa_id' => (int) $payment->empresa_id, 'sede_id' => (int) $payment->sede_id]);

            return $locked;
        }, 3);
    }

    /**
     * Ejecuta la operación anularReembolso definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $refund  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $reason  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Reembolso Resultado de la operación.
     */
    public function anularReembolso(Reembolso $refund, User $actor, string $reason, ?string $ip): Reembolso
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.reembolsos.anular';
        $this->authorization->assertAllowed($actor, $permission);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($refund, $actor, $reason, $ip, $permission): Reembolso {
            // Prepara en $locked la consulta que obtiene o modifica los datos necesarios.
            $locked = Reembolso::query()->whereKey($refund->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $payment la consulta que obtiene o modifica los datos necesarios.
            $payment = Pago::query()->whereKey($locked->pago_id)->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->authorization->allowsRecord($actor, $permission, (int) $payment->empresa_id, (int) $payment->sede_id), 403);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($locked->estado === 'anulado') {
                return $locked;
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($locked->estado === 'pendiente', 409, 'Solo se pueden anular solicitudes pendientes.');
            $locked->update(['estado' => 'anulado']);
            $this->audit->record((int) $actor->getKey(), 'reembolso.anulado', 'reembolsos', (int) $locked->getKey(),
                ['estado' => 'pendiente'], ['estado' => 'anulado'], $ip, observations: $reason,
                scope: ['empresa_id' => (int) $payment->empresa_id, 'sede_id' => (int) $payment->sede_id]);

            return $locked;
        }, 3);
    }

    /**
     * Ejecuta la operación document definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $kind  Parámetro recibido por la operación.
     * @param  mixed  $id  Parámetro recibido por la operación.
     * @return Pedido|Venta|Compra Resultado de la operación.
     */
    private function documento(string $kind, int $id): Pedido|Venta|Compra
    {
        return match ($kind) {
            'pedido' => Pedido::query()->whereKey($id)->lockForUpdate()->firstOrFail(),
            'venta' => Venta::query()->whereKey($id)->lockForUpdate()->firstOrFail(),
            'compra' => Compra::query()->whereKey($id)->lockForUpdate()->firstOrFail(),
        };
    }

    /**
     * Ejecuta la operación verificarOrigenReembolso definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $payment  Parámetro recibido por la operación.
     * @param  mixed  $return  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function verificarOrigenReembolso(Pago $payment, DevolucionGarantia $return): void
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($payment->tipo === 'cobro' && ! in_array($payment->estado, ['pendiente', 'anulado'], true),
            409, 'El pago original no es un cobro registrado.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($return->activo && $return->tipo === 'Devolución' && $return->estado === 'Resuelta'
            && trim((string) $return->resolucion) !== '', 409,
            'La devolución debe estar resuelta y documentada antes de solicitar el reembolso.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless((int) $return->empresa_id === (int) $payment->empresa_id
            && (int) $return->sede_id === (int) $payment->sede_id, 403);
        $sameSale = $return->venta_id !== null && (int) $payment->venta_id === (int) $return->venta_id;
        $sameOrder = $return->venta_id === null && (int) $payment->pedido_id === (int) $return->pedido_id;
        $saleOrder = $return->venta_id === null && $payment->venta_id !== null
            && Venta::query()->whereKey($payment->venta_id)->where('pedido_id', $return->pedido_id)->exists();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($sameSale || $sameOrder || $saleOrder, 422,
            'El cobro original no corresponde al pedido o venta de la devolución.');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $payment  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function tieneEvidenciaPago(Pago $payment): bool
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($payment->sesion_caja_id === null) {
            return false;
        }
        // Prepara en $cashIncome la consulta que obtiene o modifica los datos necesarios.
        $cashIncome = DB::table('movimientos_caja as movimiento')
            ->join('sesiones_caja as sesion', 'sesion.id', '=', 'movimiento.sesion_caja_id')
            ->join('cajas as caja', 'caja.id', '=', 'sesion.caja_id')
            ->where('movimiento.pago_id', $payment->getKey())
            ->where('movimiento.sesion_caja_id', $payment->sesion_caja_id)
            ->where('caja.sede_id', $payment->sede_id)
            ->where('movimiento.direccion', 'entrada')
            ->sum('movimiento.importe');

        return $this->centimos((string) $cashIncome) >= $this->centimos((string) $payment->importe);
    }

    /**
     * Ejecuta la operación cents definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $amount  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function centimos(string $amount): int
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(preg_match('/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/', $amount) === 1,
            422, 'El importe no tiene un formato válido.');
        [$units, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $units * 100 + (int) str_pad($fraction, 2, '0');
    }

    /**
     * Ejecuta la operación money definida por la clase ServicioOperacionesFinancieras.
     *
     * @param  mixed  $cents  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function importe(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
