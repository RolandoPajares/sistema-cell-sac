<?php

namespace App\Servicios;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Define la responsabilidad de la clase ServicioConsultasCuentas dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioConsultasCuentas
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private ServicioAutorizacion $autorizacion) {}

    /**
     * @return array{filas: array<int, array{celdas: array<int, mixed>, filtros: array<string, string>}>, metricas: array<int, array<string, string>>, paneles: array<int, array<string, mixed>>}
     */
    public function obtenerVista(User $usuario): array
    {
        $permiso = 'gestion.pagos.consultar';
        $this->autorizacion->assertAllowed($usuario, $permiso);
        $sedes = $this->autorizacion->authorizedSiteIds($usuario, $permiso);

        if ($sedes === []) {
            throw new HttpException(403, 'No tienes sedes autorizadas para consultar estas cuentas.');
        }
        $monedasAutorizadas = DB::table('sedes')->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->whereIn('sedes.id', $sedes)->where('sedes.activa', true)->where('empresas.activo', true)
            ->distinct()->orderBy('empresas.moneda_base')->pluck('empresas.moneda_base')->all();

        $aplicacionesCobro = DB::table('aplicaciones_cobro as aplicaciones')
            ->join('pagos as pagos_aplicados', 'pagos_aplicados.id', '=', 'aplicaciones.pago_id')
            ->select('aplicaciones.cuenta_cobrar_id')
            ->selectRaw('SUM(aplicaciones.importe_aplicado) AS total_aplicado')
            ->where('pagos_aplicados.tipo', 'cobro')
            ->where('pagos_aplicados.estado', 'registrado')
            ->groupBy('aplicaciones.cuenta_cobrar_id');

        $porCobrar = DB::table('cuentas_por_cobrar as cuentas')
            ->join('documentos_electronicos as documentos', 'documentos.id', '=', 'cuentas.documento_electronico_id')
            ->join('sedes as sedes_documento', 'sedes_documento.id', '=', 'documentos.sede_id')
            ->leftJoinSub($aplicacionesCobro, 'aplicaciones', 'aplicaciones.cuenta_cobrar_id', '=', 'cuentas.id')
            ->whereIn('documentos.sede_id', $sedes)
            ->whereColumn('documentos.empresa_id', 'sedes_documento.empresa_id')
            ->get([
                'cuentas.id', 'cuentas.documento_electronico_id', 'cuentas.monto_original', 'cuentas.moneda', 'cuentas.vence_en', 'cuentas.estado',
                'documentos.serie', 'documentos.correlativo', 'documentos.fecha_emision',
                'documentos.receptor_historico', 'documentos.empresa_id', 'documentos.sede_id',
                DB::raw('COALESCE(aplicaciones.total_aplicado, 0) AS total_aplicado'),
            ])
            ->map(fn (object $cuenta): array => $this->formatearFila(
                $cuenta,
                'Por cobrar',
                $this->nombreReceptor($cuenta->receptor_historico),
                trim($cuenta->serie.'-'.str_pad((string) $cuenta->correlativo, 8, '0', STR_PAD_LEFT)),
            ));

        $aplicacionesPago = DB::table('aplicaciones_pago as aplicaciones')
            ->join('pagos as pagos_aplicados', 'pagos_aplicados.id', '=', 'aplicaciones.pago_id')
            ->select('aplicaciones.cuenta_pagar_id')
            ->selectRaw('SUM(aplicaciones.importe_aplicado) AS total_aplicado')
            ->where('pagos_aplicados.tipo', 'pago')
            ->where('pagos_aplicados.estado', 'registrado')
            ->groupBy('aplicaciones.cuenta_pagar_id');

        $porPagar = DB::table('cuentas_por_pagar as cuentas')
            ->join('compras', 'compras.id', '=', 'cuentas.compra_id')
            ->join('sedes as sedes_compra', 'sedes_compra.id', '=', 'compras.sede_id')
            ->join('proveedores', 'proveedores.id', '=', 'compras.proveedor_id')
            ->leftJoinSub($aplicacionesPago, 'aplicaciones', 'aplicaciones.cuenta_pagar_id', '=', 'cuentas.id')
            ->whereIn('compras.sede_id', $sedes)
            ->where('compras.activo', true)
            ->whereColumn('compras.empresa_id', 'sedes_compra.empresa_id')
            ->get([
                'cuentas.id', 'cuentas.compra_id', 'cuentas.monto_original', 'cuentas.moneda', 'cuentas.vence_en', 'cuentas.estado',
                'compras.numero_referencia', 'compras.fecha_compra', 'compras.empresa_id', 'compras.sede_id',
                'proveedores.nombre as nombre_proveedor',
                DB::raw('COALESCE(aplicaciones.total_aplicado, 0) AS total_aplicado'),
            ])
            ->map(fn (object $cuenta): array => $this->formatearFila(
                $cuenta,
                'Por pagar',
                $cuenta->nombre_proveedor,
                $cuenta->numero_referencia ?: 'Compra #'.$cuenta->id,
            ));

        $filas = $porCobrar->concat($porPagar)->sortByDesc('fecha_orden')->values();
        $hoy = Carbon::today();
        $cobrosVigentes = $porCobrar->filter(fn (array $fila): bool => $fila['saldo'] > 0 && $fila['vence_orden'] >= $hoy->toDateString());
        $cobrosVencidos = $porCobrar->filter(fn (array $fila): bool => $fila['saldo'] > 0 && $fila['vence_orden'] < $hoy->toDateString());
        $pagosVigentes = $porPagar->filter(fn (array $fila): bool => $fila['saldo'] > 0 && $fila['vence_orden'] >= $hoy->toDateString());
        $pagosVencidos = $porPagar->filter(fn (array $fila): bool => $fila['saldo'] > 0 && $fila['vence_orden'] < $hoy->toDateString());

        $totalCuentasCobrar = max(1, $porCobrar->count());

        return [
            'filas' => $filas->map(fn (array $fila): array => [
                'celdas' => [
                    $fila['documento'], $fila['tercero'], ['texto' => $fila['tipo'], 'tono' => $fila['tipo'] === 'Por cobrar' ? 'azul' : 'violeta'],
                    $fila['emision'], $fila['vence'], $this->formatearImporte($fila['saldo'], $fila['moneda']), $fila['moneda'],
                    ['texto' => $fila['estado'], 'tono' => $fila['tono']],
                ],
                'filtros' => ['tipo' => $fila['tipo'], 'estado' => $fila['estado'], 'moneda' => $fila['moneda']],
                'cuenta_id' => $fila['id'], 'tipo_cuenta' => $fila['tipo'], 'origen_id' => $fila['origen_id'],
            ])->all(),
            'metricas' => [
                $this->metricaReal('Por cobrar vigente', $cobrosVigentes, 'bi-person-check', 'azul', $monedasAutorizadas),
                $this->metricaReal('Por cobrar vencido', $cobrosVencidos, 'bi-exclamation-triangle', 'rojo', $monedasAutorizadas),
                $this->metricaReal('Por pagar próximo', $pagosVigentes, 'bi-calendar3', 'verde', $monedasAutorizadas),
                $this->metricaReal('Por pagar vencido', $pagosVencidos, 'bi-exclamation-circle', 'ambar', $monedasAutorizadas),
            ],
            'paneles' => [
                ['titulo' => 'Cuentas por cobrar', 'icono' => 'bi-pie-chart', 'tipo' => 'anillo', 'valor' => (string) $porCobrar->count(), 'unidad' => 'Documentos', 'filas' => [['Vigentes', (string) $cobrosVigentes->count()], ['Vencidas', (string) $cobrosVencidos->count()], ['Sin saldo', (string) $porCobrar->filter(fn (array $fila): bool => $fila['saldo'] <= 0)->count()]], 'gradiente' => $this->gradienteSaldos($cobrosVigentes->count(), $cobrosVencidos->count(), $porCobrar->filter(fn (array $fila): bool => $fila['saldo'] <= 0)->count(), $totalCuentasCobrar)],
                ['titulo' => 'Cuentas por pagar', 'icono' => 'bi-bar-chart', 'tipo' => 'columnas', 'filas' => [['Vigentes', $this->importeAgrupado($pagosVigentes, $monedasAutorizadas), $this->porcentaje($pagosVigentes->count(), $porPagar->count())], ['Vencidas', $this->importeAgrupado($pagosVencidos, $monedasAutorizadas), $this->porcentaje($pagosVencidos->count(), $porPagar->count())]]],
                ['titulo' => 'Resumen de saldos', 'icono' => 'bi-exclamation-triangle', 'tipo' => 'lista', 'filas' => [['Por cobrar', (string) $porCobrar->count().' documentos'], ['Por pagar', (string) $porPagar->count().' documentos'], ['Registros visibles', (string) $filas->count()]]],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function formatearFila(object $cuenta, string $tipo, string $tercero, string $documento): array
    {
        $saldo = max(0, round((float) $cuenta->monto_original - (float) $cuenta->total_aplicado, 2));
        $fechaVencimiento = (string) $cuenta->vence_en;
        $estado = $saldo <= 0 ? 'Pagada' : ($fechaVencimiento < Carbon::today()->toDateString() ? 'Vencida' : ((float) $cuenta->total_aplicado > 0 ? 'Parcial' : (string) $cuenta->estado));

        return [
            'id' => (int) $cuenta->id,
            'origen_id' => (int) ($cuenta->compra_id ?? $cuenta->documento_electronico_id),
            'documento' => $documento, 'tercero' => $tercero ?: 'Sin nombre en el documento', 'tipo' => $tipo,
            'emision' => Carbon::parse($cuenta->fecha_emision ?? $cuenta->fecha_compra)->format('d/m/Y'),
            'vence' => Carbon::parse($fechaVencimiento)->format('d/m/Y'), 'saldo' => $saldo,
            'moneda' => (string) $cuenta->moneda, 'estado' => $estado,
            'tono' => $estado === 'Vencida' ? 'rojo' : ($estado === 'Pagada' ? 'verde' : ($estado === 'Parcial' ? 'violeta' : 'azul')),
            'vence_orden' => $fechaVencimiento, 'fecha_orden' => (string) ($cuenta->fecha_emision ?? $cuenta->fecha_compra),
        ];
    }

    /**
     * Ejecuta la operación nombreReceptor definida por la clase ServicioConsultasCuentas.
     *
     * @param  mixed  $datos  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function nombreReceptor(string $datos): string
    {
        $receptor = json_decode($datos, true);
        if (! is_array($receptor)) {
            return '';
        }

        return (string) ($receptor['razon_social'] ?? $receptor['nombre_contacto'] ?? $receptor['nombre'] ?? '');
    }

    /** @param Collection<int, array<string, mixed>> $cuentas
     * @return array<string, string>
     */
    private function metricaReal(string $titulo, Collection $cuentas, string $icono, string $tono, array $monedasAutorizadas): array
    {
        return ['titulo' => $titulo, 'valor' => $this->importeAgrupado($cuentas, $monedasAutorizadas), 'icono' => $icono, 'tono' => $tono, 'detalle' => $cuentas->count().' documentos'];
    }

    /** @param array<int, string> $monedasAutorizadas */
    private function importeAgrupado(Collection $cuentas, array $monedasAutorizadas): string
    {
        if ($cuentas->isEmpty()) {
            return collect($monedasAutorizadas)->map(fn (string $moneda): string => $this->formatearImporte(0, $moneda))->implode(' · ');
        }

        return $cuentas->groupBy('moneda')
            ->map(fn (Collection $grupo, string $moneda): string => $this->formatearImporte((float) $grupo->sum('saldo'), $moneda))
            ->implode(' · ');
    }

    /**
     * Ejecuta la operación formatearImporte definida por la clase ServicioConsultasCuentas.
     *
     * @param  mixed  $importe  Parámetro recibido por la operación.
     * @param  mixed  $moneda  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function formatearImporte(float $importe, string $moneda): string
    {
        return $moneda.' '.number_format($importe, 2, ',', ' ');
    }

    /**
     * Ejecuta la operación porcentaje definida por la clase ServicioConsultasCuentas.
     *
     * @param  mixed  $parte  Parámetro recibido por la operación.
     * @param  mixed  $total  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function porcentaje(int $parte, int $total): int
    {
        return $total === 0 ? 0 : (int) round(($parte / $total) * 100);
    }

    /**
     * Ejecuta la operación gradienteSaldos definida por la clase ServicioConsultasCuentas.
     *
     * @param  mixed  $vigentes  Parámetro recibido por la operación.
     * @param  mixed  $vencidos  Parámetro recibido por la operación.
     * @param  mixed  $pagados  Parámetro recibido por la operación.
     * @param  mixed  $total  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function gradienteSaldos(int $vigentes, int $vencidos, int $pagados, int $total): string
    {
        $porcentajeVigentes = $this->porcentaje($vigentes, $total);
        $porcentajeVencidos = $porcentajeVigentes + $this->porcentaje($vencidos, $total);

        return 'conic-gradient(#0950c6 0 '.$porcentajeVigentes.'%, #f34d61 '.$porcentajeVigentes.'% '.$porcentajeVencidos.'%, #29b66a '.$porcentajeVencidos.'% 100%)';
    }
}
