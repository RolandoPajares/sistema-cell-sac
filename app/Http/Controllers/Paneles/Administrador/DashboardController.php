<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Paneles\PanelDashboardController;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Proveedor;
use App\Models\Marketing\Campania;
use App\Models\User;
use App\Servicios\ConsultaVentas;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioPaneles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DashboardController extends PanelDashboardController
{
    public function __construct(
        ServicioPaneles $panelService,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioInventario $servicioInventario,
        private readonly ConsultaVentas $consultaVentas,
    ) {
        parent::__construct($panelService);
    }

    protected function role(): string
    {
        return 'administrador';
    }

    protected function viewName(): string
    {
        return 'paneles.administrador.dashboard';
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(Request $request, User $user): array
    {
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $consultaPedidos = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            Pedido::query(), $user, 'gestion.pedidos.consultar', 'usuario_id',
        );
        $consultaProductos = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            Producto::query(), $user, 'gestion.productos.consultar',
        );
        $consultaProveedores = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            Proveedor::query(), $user, 'gestion.proveedores.consultar',
        );
        $consultaClientes = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            Cliente::query(), $user, 'gestion.clientes.consultar',
        );
        $consultaCampanias = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            Campania::query(), $user, 'gestion.campanias.consultar',
        );
        $consultaAuditoria = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            RegistroAuditoria::query(), $user, 'gestion.auditoria.consultar',
        );
        $alcanceInventario = $this->servicioInventario->alcanceConsulta($user);
        $inicioGraficaVentas = $now->copy()->startOfMonth()->subMonths(11);
        $totalesVentasMensuales = $this->consultaVentas->obtenerTotalesMensualesConfirmados(
            $user,
            $inicioGraficaVentas,
            $currentMonthEnd,
        );

        $salesThisMonth = $totalesVentasMensuales->get($now->format('Y-m'), 0.0);
        $salesPreviousMonth = $totalesVentasMensuales->get($previousMonthStart->format('Y-m'), 0.0);

        $ordersCount = $consultaPedidos()->count();
        $ordersThisMonth = $consultaPedidos()
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $ordersPreviousMonth = $consultaPedidos()
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $activeProducts = $consultaProductos()->where('activo', true)->count();
        $productsAddedThisMonth = $consultaProductos()
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $productosSinExistencias = $this->servicioInventario->resumenConsulta($alcanceInventario)['sin_existencias'];
        $suppliersCount = $consultaProveedores()->where('activo', true)->count();

        $newCustomers = $consultaClientes()
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $newCustomersPreviousMonth = $consultaClientes()
            ->where('activo', true)
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $monthlySales = $this->monthlySales($totalesVentasMensuales, $now);
        $maxMonthlySales = max(1.0, (float) max(array_column($monthlySales, 'value')));
        $salesChartMax = max(5000.0, ceil($maxMonthlySales / 5000) * 5000);

        $statusColors = [
            'Pendiente' => '#2d83f5',
            'Pagado' => '#20c985',
            'Enviado' => '#8b5cf6',
            'En proceso' => '#f59e0b',
            'Cancelado' => '#ff4d5e',
        ];
        $orderStatuses = $consultaPedidos()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $status): array => [
                'label' => (string) $status->estado,
                'total' => (int) $status->total,
                'color' => $statusColors[$status->estado] ?? '#8aa3c4',
            ])
            ->values();
        $orderStatusTotal = (int) $orderStatuses->sum('total');
        $orderStatusChart = $this->statusChart($orderStatuses, $orderStatusTotal);

        $recentOrders = $consultaPedidos()
            ->with('user:id,nombre,correo')
            ->latest('creado_en')
            ->latest('id')
            ->limit(5)
            ->get();
        $productosSinExistenciasListado = $this->servicioInventario
            ->productosParaConsulta($alcanceInventario, ['estado' => 'sin_existencias'], true)
            ->orderBy('existencias_alcance')
            ->orderBy('productos.nombre')
            ->limit(5)
            ->get();
        $topProducts = $this->consultaVentas->obtenerProductosMasVendidos($user);

        $campaigns = $consultaCampanias()
            ->where('activo', true)
            ->orderByDesc('vistas')
            ->limit(4)
            ->get(['id', 'nombre', 'titulo', 'vistas', 'clics', 'finaliza_en']);
        $campaignMaxViews = max(1, (int) $campaigns->max('vistas'));

        $recentActivity = $consultaAuditoria()
            ->with('user:id,nombre')
            ->latest('creado_en')
            ->limit(6)
            ->get(['id', 'usuario_id', 'accion', 'entidad', 'entidad_id', 'creado_en']);

        $pendingOrders = $consultaPedidos()
            ->whereIn('estado', ['Pendiente', 'En proceso'])
            ->count();
        $expiringCampaigns = $consultaCampanias()
            ->where('activo', true)
            ->whereBetween('finaliza_en', [$now, $now->copy()->addDays(7)])
            ->count();
        $reminders = $this->reminders($productosSinExistencias, $pendingOrders, $expiringCampaigns);

        return [
            'role' => $user->rol,
            'dashboardNow' => $now,
            'products' => $activeProducts,
            'orders' => $ordersCount,
            'suppliers' => $suppliersCount,
            'salesThisMonth' => $salesThisMonth,
            'salesTrendPercent' => $this->trendPercent($salesThisMonth, $salesPreviousMonth),
            'ordersCount' => $ordersCount,
            'ordersThisMonth' => $ordersThisMonth,
            'ordersTrendPercent' => $this->trendPercent($ordersThisMonth, $ordersPreviousMonth),
            'activeProducts' => $activeProducts,
            'productsAddedThisMonth' => $productsAddedThisMonth,
            'lowStock' => $productosSinExistencias,
            'newCustomers' => $newCustomers,
            'newCustomersTrendPercent' => $this->trendPercent($newCustomers, $newCustomersPreviousMonth),
            'monthlySales' => $monthlySales,
            'maxMonthlySales' => $maxMonthlySales,
            'salesChartMax' => $salesChartMax,
            'orderStatuses' => $orderStatuses,
            'orderStatusTotal' => $orderStatusTotal,
            'orderStatusChart' => $orderStatusChart,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $productosSinExistenciasListado,
            'topProducts' => $topProducts,
            'campaigns' => $campaigns,
            'campaignMaxViews' => $campaignMaxViews,
            'recentActivity' => $recentActivity,
            'reminders' => $reminders,
        ];
    }

    /**
     * @return array<int, array{key:string, label:string, value:float}>
     */
    private function monthlySales(Collection $totales, Carbon $now): array
    {
        $start = $now->copy()->startOfMonth()->subMonths(11);

        $labels = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];
        $months = [];
        $cursor = $start->copy();

        for ($index = 0; $index < 12; $index++) {
            $key = $cursor->format('Y-m');
            $months[] = [
                'key' => $key,
                'label' => $labels[(int) $cursor->format('n')],
                'value' => (float) ($totales[$key] ?? 0),
            ];
            $cursor->addMonth();
        }

        return $months;
    }

    private function trendPercent(float|int $current, float|int $previous): ?float
    {
        if ((float) $previous === 0.0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param  Collection<int, array{label:string, total:int, color:string}>  $statuses
     */
    private function statusChart(Collection $statuses, int $total): string
    {
        if ($total === 0) {
            return 'conic-gradient(#dce9f6 0 100%)';
        }

        $start = 0.0;
        $stops = [];

        foreach ($statuses as $status) {
            $end = $start + (($status['total'] / $total) * 100);
            $stops[] = sprintf('%s %.2f%% %.2f%%', $status['color'], $start, $end);
            $start = $end;
        }

        return 'conic-gradient('.implode(', ', $stops).')';
    }

    /**
     * @return Collection<int, array{icon:string, title:string, detail:string, tone:string}>
     */
    private function reminders(int $lowStock, int $pendingOrders, int $expiringCampaigns): Collection
    {
        $items = collect();

        if ($lowStock > 0) {
            $items->push([
                'icon' => 'bi-exclamation-triangle',
                'title' => 'Revisar stock de productos',
                'detail' => $lowStock.' producto(s) con stock crítico',
                'tone' => 'danger',
            ]);
        }

        if ($pendingOrders > 0) {
            $items->push([
                'icon' => 'bi-clock-history',
                'title' => 'Procesar pedidos pendientes',
                'detail' => $pendingOrders.' pedido(s) en espera',
                'tone' => 'info',
            ]);
        }

        if ($expiringCampaigns > 0) {
            $items->push([
                'icon' => 'bi-megaphone',
                'title' => 'Revisar campañas próximas a finalizar',
                'detail' => $expiringCampaigns.' campaña(s) finalizan en 7 días',
                'tone' => 'warning',
            ]);
        }

        return $items;
    }
}
