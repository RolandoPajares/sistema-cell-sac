<?php

namespace App\Servicios\Compartidos\Comercial;

use App\Models\Comercio\DetalleVenta;
use App\Models\Comercio\Venta;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ConsultaVentas dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ConsultaVentas
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /** @return Builder<Venta> */
    public function query(User $usuario, array $filtros): Builder
    {
        return $this->autorizacion->scopeRecordsFor(Venta::query(), $usuario, 'gestion.ventas.consultar')
            ->when(isset($filtros['from'], $filtros['to']), fn (Builder $query): Builder => $query
                ->whereBetween('ventas.fecha_venta', [$filtros['from'], $filtros['to']]))
            ->when($filtros['buscar'] ?? null, function (Builder $query, string $busqueda): void {
                $query->where(function (Builder $query) use ($busqueda): void {
                    $query->where('ventas.codigo', 'like', '%'.$busqueda.'%')
                        ->orWhereHas('cliente', fn (Builder $cliente) => $cliente
                            ->where('razon_social', 'like', '%'.$busqueda.'%')
                            ->orWhere('nombre_contacto', 'like', '%'.$busqueda.'%'))
                        ->orWhereHas('usuario', fn (Builder $usuario) => $usuario->where('nombre', 'like', '%'.$busqueda.'%'));
                });
            })
            ->when($filtros['estado'] ?? null, fn (Builder $query, string $estado) => $query->where('ventas.estado', $estado))
            ->when($filtros['cliente_id'] ?? null, fn (Builder $query, int $clienteId) => $query->where('ventas.cliente_id', $clienteId))
            ->when($filtros['sede_id'] ?? null, fn (Builder $query, int $sedeId) => $query->where('ventas.sede_id', $sedeId))
            ->when($filtros['canal'] ?? null, function (Builder $query, string $canal): void {
                if (! Schema::hasColumn('ventas', 'canal')) {
                    $query->whereHas('pedido', fn (Builder $pedido) => $pedido->where('canal', $canal));

                    return;
                }
                $query->where(fn (Builder $subconsulta) => $subconsulta->where('ventas.canal', $canal)
                    ->orWhere(fn (Builder $sinCanal) => $sinCanal->whereNull('ventas.canal')->whereHas('pedido', fn (Builder $pedido) => $pedido->where('canal', $canal))));
            });
    }

    /**
     * @return Collection<string, float>
     */
    public function obtenerTotalesMensualesConfirmados(User $usuario, Carbon $desde, Carbon $hasta): Collection
    {
        $expresionMes = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', ventas.fecha_venta)"
            : "DATE_FORMAT(ventas.fecha_venta, '%Y-%m')";

        return $this->query($usuario, [
            'from' => $desde,
            'to' => $hasta,
            'estado' => 'confirmada',
        ])
            ->where('ventas.moneda', 'PEN')
            ->selectRaw($expresionMes.' AS periodo, COALESCE(SUM(ventas.total), 0) AS importe_total')
            ->groupByRaw($expresionMes)
            ->orderBy('periodo')
            ->pluck('importe_total', 'periodo')
            ->map(static fn (mixed $importe): float => (float) $importe);
    }

    /**
     * @return Collection<int, DetalleVenta>
     */
    public function obtenerProductosMasVendidos(User $usuario): Collection
    {
        $ventasConfirmadas = $this->query($usuario, ['estado' => 'confirmada'])
            ->where('ventas.moneda', 'PEN')
            ->select('ventas.id');

        return DetalleVenta::query()
            ->whereIn('venta_id', $ventasConfirmadas)
            ->selectRaw('producto_id, descripcion_historica, SUM(cantidad) AS unidades_vendidas, SUM(total_linea) AS importe_vendido')
            ->with('producto:id,nombre,marca,url_imagen')
            ->groupBy('producto_id', 'descripcion_historica')
            ->orderByDesc('unidades_vendidas')
            ->limit(5)
            ->get();
    }

    /**
     * Ejecuta la operación resumen definida por la clase ConsultaVentas.
     *
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $filtros  Parámetro recibido por la operación.
     * @return array Resultado de la operación.
     */
    public function resumen(User $usuario, array $filtros): array
    {
        $query = $this->query($usuario, $filtros);
        $totales = (clone $query)->selectRaw('COUNT(*) AS cantidad, COALESCE(SUM(ventas.total), 0) AS total, COUNT(DISTINCT ventas.cliente_id) AS clientes')->first();
        $cantidad = (int) $totales->cantidad;
        $total = (float) $totales->total;
        $monedas = (clone $query)->distinct()->pluck('ventas.moneda');
        $monedasMultiples = $monedas->count() > 1;
        $campoCanal = Schema::hasColumn('ventas', 'canal') ? 'COALESCE(ventas.canal, pedidos.canal)' : 'pedidos.canal';
        $canales = (clone $query)->leftJoin('pedidos', 'pedidos.id', '=', 'ventas.pedido_id')
            ->selectRaw($campoCanal.' AS canal, COUNT(*) AS cantidad')->groupByRaw($campoCanal)->get();
        $colores = ['ecommerce' => '#1784f5', 'presencial' => '#15a774'];
        $resumenCanales = $canales->map(static fn ($fila): array => [
            'label' => $fila->canal === null ? 'Sin canal registrado' : (string) $fila->canal,
            'count' => (int) $fila->cantidad,
            'percent' => $cantidad > 0 ? ((int) $fila->cantidad * 100 / $cantidad) : 0,
            'color' => $colores[$fila->canal] ?? '#6c7d90',
        ])->all();
        $porDia = (clone $query)->selectRaw('DATE(ventas.fecha_venta) AS dia, COUNT(*) AS cantidad, COALESCE(SUM(ventas.total), 0) AS total')
            ->groupByRaw('DATE(ventas.fecha_venta)')->orderBy('dia')->get();
        $puntos = $porDia->map(static fn ($fila): array => [
            'label' => (string) $fila->dia, 'sales' => (float) $fila->total, 'orders' => (int) $fila->cantidad,
        ])->all();
        if ($puntos === []) {
            $puntos = [['label' => 'Sin registros', 'sales' => 0.0, 'orders' => 0]];
        }
        $maximoTotal = max(1, ...array_column($puntos, 'sales'));
        $maximoCantidad = max(1, ...array_column($puntos, 'orders'));
        $monedaReporte = $monedas->first() ?? 'PEN';
        $tendencia = array_map(static fn (array $punto): array => $punto + [
            'sales_height' => $punto['sales'] / $maximoTotal * 100,
            'orders_height' => $punto['orders'] / $maximoCantidad * 100,
            'sales_label' => self::formatCurrency($punto['sales'], $monedaReporte),
        ], $puntos);
        $channelSegments = [];
        $channelStart = 0;
        foreach ($resumenCanales as $channel) {
            $channelEnd = $channelStart + $channel['percent'];
            $channelSegments[] = $channel['color'].' '.$channelStart.'% '.$channelEnd.'%';
            $channelStart = $channelEnd;
        }
        $detalles = DetalleVenta::query()->whereIn('venta_id', (clone $query)->select('ventas.id'))
            ->selectRaw('producto_id, descripcion_historica, SUM(cantidad) AS unidades, SUM(total_linea) AS importe')
            ->groupBy('producto_id', 'descripcion_historica')->orderByDesc('importe')->limit(5)->get();

        return [
            'ordersCount' => $cantidad, 'realizedSales' => $cantidad, 'totalSales' => $monedasMultiples ? null : $total,
            'totalSalesLabel' => self::formatCurrency($monedasMultiples ? null : $total, $monedaReporte),
            'monedasMultiples' => $monedasMultiples, 'monedaReporte' => $monedaReporte,
            'customersServed' => (int) $totales->clientes, 'averageTicket' => $monedasMultiples ? null : ($cantidad ? $total / $cantidad : 0),
            'averageTicketLabel' => self::formatCurrency($monedasMultiples ? null : ($cantidad ? $total / $cantidad : 0), $monedaReporte),
            'chartMaxLabel' => self::formatCurrency($maximoTotal, $monedaReporte),
            'chartMidLabel' => self::formatCurrency($maximoTotal / 2, $monedaReporte),
            'chartZeroLabel' => self::formatCurrency(0, $monedaReporte),
            'channelDonut' => 'background: conic-gradient('.implode(', ', $channelSegments ?: ['#d9e7f6 0% 100%']).');',
            'salesTrend' => $tendencia, 'channelSummary' => $resumenCanales,
            'topProducts' => $detalles->map(static fn ($detalle): array => [
                'name' => $detalle->descripcion_historica, 'units' => (float) $detalle->unidades,
                'revenue' => (float) $detalle->importe, 'revenue_label' => self::formatCurrency((float) $detalle->importe, $monedaReporte), 'image' => null,
            ])->all(),
        ];
    }

    private static function formatCurrency(float|int|string|null $value, string $currency): string
    {
        if ($value === null) {
            return 'Monedas múltiples';
        }

        return ($currency === 'PEN' ? 'S/ ' : $currency.' ').number_format((float) $value, 2);
    }
}
