<?php

namespace App\Servicios\Gestion\Finanzas\CuentasCobrarPagar;

use App\Models\Compras\Compra;
use App\Models\Finanzas\Pago;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Support\Facades\DB;

/**
 * Define la responsabilidad de la clase ServicioCuentasFinancieras dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioCuentasFinancieras
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly RegistradorAuditoria $auditoria,
    ) {}

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $compraId  Parámetro recibido por la operación.
     * @param  mixed  $venceEn  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    public function crearCuentaPorPagar(int $compraId, string $venceEn, User $usuario, ?string $ip): int
    {
        $permiso = 'gestion.pagos.crear';
        $this->autorizacion->assertAllowed($usuario, $permiso);

        return DB::transaction(function () use ($compraId, $venceEn, $usuario, $ip, $permiso): int {
            $compra = Compra::query()->whereKey($compraId)->lockForUpdate()->firstOrFail();
            abort_unless($compra->activo && $compra->estado === 'Recibida' && (float) $compra->total > 0, 409,
                'Solo una compra activa y recibida con total mayor que cero puede generar una cuenta por pagar.');
            abort_unless($this->autorizacion->allowsRecord($usuario, $permiso, (int) $compra->empresa_id, (int) $compra->sede_id), 403);

            $existente = DB::table('cuentas_por_pagar')->where('compra_id', $compra->getKey())->lockForUpdate()->first();
            if ($existente !== null) {
                abort_unless($existente->moneda === $compra->moneda && (float) $existente->monto_original === (float) $compra->total
                    && $existente->vence_en === $venceEn, 409, 'La compra ya tiene una cuenta con condiciones distintas.');

                return (int) $existente->id;
            }

            $cuentaId = (int) DB::table('cuentas_por_pagar')->insertGetId([
                'compra_id' => $compra->getKey(), 'monto_original' => $compra->total, 'moneda' => $compra->moneda,
                'vence_en' => $venceEn, 'estado' => 'pendiente', 'creado_en' => now(),
            ]);
            $this->auditoria->record((int) $usuario->getKey(), 'cuenta_por_pagar.creada', 'cuentas_por_pagar', $cuentaId,
                null, ['compra_id' => $compra->getKey(), 'monto_original' => $compra->total, 'moneda' => $compra->moneda, 'vence_en' => $venceEn], $ip,
                scope: ['empresa_id' => (int) $compra->empresa_id, 'sede_id' => (int) $compra->sede_id]);

            return $cuentaId;
        }, 3);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $cuentaId  Parámetro recibido por la operación.
     * @param  mixed  $pagoId  Parámetro recibido por la operación.
     * @param  mixed  $importe  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function aplicarPagoRegistrado(int $cuentaId, int $pagoId, string $importe, User $usuario, ?string $ip): void
    {
        $permiso = 'gestion.pagos.actualizar';
        $this->autorizacion->assertAllowed($usuario, $permiso);
        $centimosSolicitados = $this->centimos($importe);
        abort_if($centimosSolicitados <= 0, 422, 'El importe aplicado debe ser mayor que cero.');

        DB::transaction(function () use ($cuentaId, $pagoId, $importe, $centimosSolicitados, $usuario, $ip, $permiso): void {
            $cuenta = DB::table('cuentas_por_pagar')->where('id', $cuentaId)->lockForUpdate()->first();
            abort_unless($cuenta !== null, 404);
            $compra = Compra::query()->whereKey($cuenta->compra_id)->lockForUpdate()->firstOrFail();
            $pago = Pago::query()->whereKey($pagoId)->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($usuario, $permiso, (int) $compra->empresa_id, (int) $compra->sede_id), 403);
            abort_unless($pago->tipo === 'pago' && $pago->estado === 'registrado'
                && (int) $pago->compra_id === (int) $compra->getKey()
                && (int) $pago->empresa_id === (int) $compra->empresa_id
                && (int) $pago->sede_id === (int) $compra->sede_id
                && $pago->moneda === $cuenta->moneda, 409,
                'Solo se puede aplicar un pago registrado de la misma compra, empresa, sede y moneda.');

            $aplicacionExistente = DB::table('aplicaciones_pago')->where('pago_id', $pagoId)
                ->where('cuenta_pagar_id', $cuentaId)->lockForUpdate()->first();
            if ($aplicacionExistente !== null) {
                abort_unless($this->centimos((string) $aplicacionExistente->importe_aplicado) === $centimosSolicitados, 409,
                    'El pago ya se aplicó a esta cuenta con otro importe.');

                return;
            }

            $aplicadoDePago = DB::table('aplicaciones_pago')->join('pagos', 'pagos.id', '=', 'aplicaciones_pago.pago_id')
                ->where('aplicaciones_pago.pago_id', $pagoId)->where('pagos.estado', 'registrado')->sum('importe_aplicado');
            $saldoPago = $this->centimos((string) $pago->importe) - $this->centimos((string) $aplicadoDePago);
            $aplicadoDeCuenta = DB::table('aplicaciones_pago')->join('pagos', 'pagos.id', '=', 'aplicaciones_pago.pago_id')
                ->where('aplicaciones_pago.cuenta_pagar_id', $cuentaId)->where('pagos.estado', 'registrado')->sum('importe_aplicado');
            $saldoCuenta = $this->centimos((string) $cuenta->monto_original) - $this->centimos((string) $aplicadoDeCuenta);
            abort_if($centimosSolicitados > $saldoPago || $centimosSolicitados > $saldoCuenta, 422,
                'El importe supera el saldo no aplicado del pago o de la cuenta.');

            DB::table('aplicaciones_pago')->insert([
                'pago_id' => $pagoId, 'cuenta_pagar_id' => $cuentaId, 'importe_aplicado' => $importe, 'aplicado_en' => now(),
            ]);
            $this->auditoria->record((int) $usuario->getKey(), 'cuenta_por_pagar.pago_aplicado', 'cuentas_por_pagar', $cuentaId,
                ['saldo' => number_format($saldoCuenta / 100, 2, '.', '')],
                ['pago_id' => $pagoId, 'importe_aplicado' => $importe, 'saldo' => number_format(($saldoCuenta - $centimosSolicitados) / 100, 2, '.', '')], $ip,
                scope: ['empresa_id' => (int) $compra->empresa_id, 'sede_id' => (int) $compra->sede_id]);
        }, 3);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $cuentaId  Parámetro recibido por la operación.
     * @param  mixed  $pagoId  Parámetro recibido por la operación.
     * @param  mixed  $importe  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function aplicarCobroRegistrado(int $cuentaId, int $pagoId, string $importe, User $usuario, ?string $ip): void
    {
        $permiso = 'gestion.pagos.actualizar';
        $this->autorizacion->assertAllowed($usuario, $permiso);
        $centimosSolicitados = $this->centimos($importe);
        abort_if($centimosSolicitados <= 0, 422, 'El importe aplicado debe ser mayor que cero.');

        DB::transaction(function () use ($cuentaId, $pagoId, $importe, $centimosSolicitados, $usuario, $ip, $permiso): void {
            $cuenta = DB::table('cuentas_por_cobrar')->where('id', $cuentaId)->lockForUpdate()->first();
            abort_unless($cuenta !== null, 404);
            $documento = DB::table('documentos_electronicos')->where('id', $cuenta->documento_electronico_id)->lockForUpdate()->first();
            abort_unless($documento !== null && $documento->venta_id !== null, 409,
                'La cuenta por cobrar debe estar vinculada a una venta local para aplicar cobros.');
            $pago = Pago::query()->whereKey($pagoId)->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($usuario, $permiso, (int) $documento->empresa_id, (int) $documento->sede_id), 403);
            abort_unless($pago->tipo === 'cobro' && $pago->estado === 'registrado'
                && (int) $pago->venta_id === (int) $documento->venta_id
                && (int) $pago->empresa_id === (int) $documento->empresa_id
                && (int) $pago->sede_id === (int) $documento->sede_id
                && $pago->moneda === $cuenta->moneda, 409,
                'Solo se puede aplicar un cobro registrado de la misma venta, empresa, sede y moneda.');

            $aplicacionExistente = DB::table('aplicaciones_cobro')->where('pago_id', $pagoId)
                ->where('cuenta_cobrar_id', $cuentaId)->lockForUpdate()->first();
            if ($aplicacionExistente !== null) {
                abort_unless($this->centimos((string) $aplicacionExistente->importe_aplicado) === $centimosSolicitados, 409,
                    'El cobro ya se aplicó a esta cuenta con otro importe.');

                return;
            }

            $aplicadoDelCobro = DB::table('aplicaciones_cobro')->join('pagos', 'pagos.id', '=', 'aplicaciones_cobro.pago_id')
                ->where('aplicaciones_cobro.pago_id', $pagoId)->where('pagos.estado', 'registrado')->sum('importe_aplicado');
            $saldoCobro = $this->centimos((string) $pago->importe) - $this->centimos((string) $aplicadoDelCobro);
            $aplicadoDeCuenta = DB::table('aplicaciones_cobro')->join('pagos', 'pagos.id', '=', 'aplicaciones_cobro.pago_id')
                ->where('aplicaciones_cobro.cuenta_cobrar_id', $cuentaId)->where('pagos.estado', 'registrado')->sum('importe_aplicado');
            $saldoCuenta = $this->centimos((string) $cuenta->monto_original) - $this->centimos((string) $aplicadoDeCuenta);
            abort_if($centimosSolicitados > $saldoCobro || $centimosSolicitados > $saldoCuenta, 422,
                'El importe supera el saldo no aplicado del cobro o de la cuenta.');

            DB::table('aplicaciones_cobro')->insert([
                'pago_id' => $pagoId, 'cuenta_cobrar_id' => $cuentaId, 'importe_aplicado' => $importe, 'aplicado_en' => now(),
            ]);
            $this->auditoria->record((int) $usuario->getKey(), 'cuenta_por_cobrar.cobro_aplicado', 'cuentas_por_cobrar', $cuentaId,
                ['saldo' => number_format($saldoCuenta / 100, 2, '.', '')],
                ['pago_id' => $pagoId, 'importe_aplicado' => $importe, 'saldo' => number_format(($saldoCuenta - $centimosSolicitados) / 100, 2, '.', '')], $ip,
                scope: ['empresa_id' => (int) $documento->empresa_id, 'sede_id' => (int) $documento->sede_id]);
        }, 3);
    }

    /**
     * Ejecuta la operación centimos definida por la clase ServicioCuentasFinancieras.
     *
     * @param  mixed  $importe  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function centimos(string $importe): int
    {
        abort_unless(preg_match('/\A(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?\z/', $importe) === 1, 422, 'El importe no es válido.');

        return (int) round((float) $importe * 100);
    }
}
