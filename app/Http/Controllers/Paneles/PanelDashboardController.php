<?php

namespace App\Http\Controllers\Paneles;

use App\Http\Controllers\Controller;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Proveedor;
use App\Models\Marketing\Campania;
use App\Models\User;
use App\Servicios\Compartidos\Comercial\ConsultaVentas;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Compartidos\Seguridad\ServicioMenuDinamico;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase DashboardController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class PanelDashboardController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioInventario $servicioInventario,
        private readonly ConsultaVentas $consultaVentas,
        private readonly ServicioMenuDinamico $menuDinamico,
    ) {}

    /**
     * Ejecuta la operación role definida por la clase DashboardController.
     *
     * @return string Resultado de la operación.
     */
    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return string Resultado de la operación.
     */
    final public function __invoke(Request $request): View
    {
        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($user instanceof User, 403);

        // Devuelve la vista con los datos preparados por el controlador.
        return view($this->viewName(), $this->viewData($request, $user));
    }

    protected function viewName(): string
    {
        return 'paneles.dashboard';
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(Request $request, User $user): array
    {
        // Guarda en $now el resultado necesario para continuar esta operacion.
        $now = now();
        // Guarda en $currentMonthStart el resultado necesario para continuar esta operacion.
        $currentMonthStart = $now->copy()->startOfMonth();
        // Guarda en $currentMonthEnd el resultado necesario para continuar esta operacion.
        $currentMonthEnd = $now->copy()->endOfMonth();
        // Guarda en $previousMonthStart el resultado necesario para continuar esta operacion.
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        // Guarda en $previousMonthEnd el resultado necesario para continuar esta operacion.
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
        // Guarda en $alcanceInventario el resultado necesario para continuar esta operacion.
        $alcanceInventario = $this->servicioInventario->alcanceConsulta($user);
        // Guarda en $inicioGraficaVentas el resultado necesario para continuar esta operacion.
        $inicioGraficaVentas = $now->copy()->startOfMonth()->subMonths(11);
        // Guarda en $totalesVentasMensuales el resultado necesario para continuar esta operacion.
        $totalesVentasMensuales = $this->consultaVentas->obtenerTotalesMensualesConfirmados(
            $user,
            $inicioGraficaVentas,
            $currentMonthEnd,
        );

        // Guarda en $salesThisMonth el resultado necesario para continuar esta operacion.
        $salesThisMonth = $totalesVentasMensuales->get($now->format('Y-m'), 0.0);
        // Guarda en $salesPreviousMonth el resultado necesario para continuar esta operacion.
        $salesPreviousMonth = $totalesVentasMensuales->get($previousMonthStart->format('Y-m'), 0.0);

        // Guarda en $ordersCount el resultado necesario para continuar esta operacion.
        $ordersCount = $consultaPedidos()->count();
        // Guarda en $ordersThisMonth el resultado necesario para continuar esta operacion.
        $ordersThisMonth = $consultaPedidos()
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        // Guarda en $ordersPreviousMonth el resultado necesario para continuar esta operacion.
        $ordersPreviousMonth = $consultaPedidos()
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();

        // Guarda en $activeProducts el resultado necesario para continuar esta operacion.
        $activeProducts = $consultaProductos()->where('activo', true)->count();
        // Guarda en $productsAddedThisMonth el resultado necesario para continuar esta operacion.
        $productsAddedThisMonth = $consultaProductos()
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        // Guarda en $productosSinExistencias el resultado necesario para continuar esta operacion.
        $productosSinExistencias = $this->servicioInventario->resumenConsulta($alcanceInventario)['sin_existencias'];
        // Guarda en $suppliersCount el resultado necesario para continuar esta operacion.
        $suppliersCount = $consultaProveedores()->where('activo', true)->count();

        // Guarda en $newCustomers el resultado necesario para continuar esta operacion.
        $newCustomers = $consultaClientes()
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        // Guarda en $newCustomersPreviousMonth el resultado necesario para continuar esta operacion.
        $newCustomersPreviousMonth = $consultaClientes()
            ->where('activo', true)
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();

        // Guarda en $monthlySales el resultado necesario para continuar esta operacion.
        $monthlySales = $this->ventasMensuales($totalesVentasMensuales, $now);
        // Guarda en $maxMonthlySales el resultado necesario para continuar esta operacion.
        $maxMonthlySales = max(1.0, (float) max(array_column($monthlySales, 'value')));
        // Guarda en $salesChartMax el resultado necesario para continuar esta operacion.
        $salesChartMax = max(5000.0, ceil($maxMonthlySales / 5000) * 5000);

        // Agrupa en $statusColors los datos que se utilizaran en la siguiente operacion.
        $statusColors = [
            'Pendiente' => '#2d83f5',
            'Pagado' => '#20c985',
            'Enviado' => '#8b5cf6',
            'En proceso' => '#f59e0b',
            'Cancelado' => '#ff4d5e',
        ];
        // Guarda en $orderStatuses el resultado necesario para continuar esta operacion.
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
        // Guarda en $orderStatusTotal el resultado necesario para continuar esta operacion.
        $orderStatusTotal = (int) $orderStatuses->sum('total');
        // Guarda en $orderStatusChart el resultado necesario para continuar esta operacion.
        $orderStatusChart = $this->graficoEstados($orderStatuses, $orderStatusTotal);

        // Guarda en $recentOrders el resultado necesario para continuar esta operacion.
        $recentOrders = $consultaPedidos()
            ->with(['user:id,nombre,correo', 'details.product:id,nombre'])
            ->latest('creado_en')
            ->latest('id')
            ->limit(5)
            ->get();
        // Guarda en $productosSinExistenciasListado el resultado necesario para continuar esta operacion.
        $productosSinExistenciasListado = $this->servicioInventario
            ->productosParaConsulta($alcanceInventario, ['estado' => 'sin_existencias'], true)
            ->orderBy('existencias_alcance')
            ->orderBy('productos.nombre')
            ->limit(5)
            ->get();
        // Guarda en $topProducts el resultado necesario para continuar esta operacion.
        $topProducts = $this->consultaVentas->obtenerProductosMasVendidos($user);

        // Guarda en $campaigns el resultado necesario para continuar esta operacion.
        $campaigns = $consultaCampanias()
            ->where('activo', true)
            ->orderByDesc('vistas')
            ->limit(4)
            ->get(['id', 'nombre', 'titulo', 'vistas', 'clics', 'finaliza_en']);
        // Guarda en $campaignMaxViews el resultado necesario para continuar esta operacion.
        $campaignMaxViews = max(1, (int) $campaigns->max('vistas'));
        $campaignProgress = $campaigns->mapWithKeys(static fn (Campania $campaign): array => [
            $campaign->id => min(100, round(((int) $campaign->vistas / $campaignMaxViews) * 100)),
        ])->all();

        // Guarda en $recentActivity el resultado necesario para continuar esta operacion.
        $recentActivity = $consultaAuditoria()
            ->with('user:id,nombre')
            ->latest('creado_en')
            ->limit(6)
            ->get(['id', 'usuario_id', 'accion', 'entidad', 'entidad_id', 'creado_en']);

        // Guarda en $pendingOrders el resultado necesario para continuar esta operacion.
        $pendingOrders = $consultaPedidos()
            ->whereIn('estado', ['Pendiente', 'En proceso'])
            ->count();
        // Guarda en $expiringCampaigns el resultado necesario para continuar esta operacion.
        $expiringCampaigns = $consultaCampanias()
            ->where('activo', true)
            ->whereBetween('finaliza_en', [$now, $now->copy()->addDays(7)])
            ->count();
        // Guarda en $reminders el resultado necesario para continuar esta operacion.
        $reminders = $this->recordatorios($productosSinExistencias, $pendingOrders, $expiringCampaigns);
        // Guarda en $quickLinks el resultado necesario para continuar esta operacion.
        $quickLinks = collect($this->menuDinamico->paraUsuario($user)['grupos'])
            ->flatMap(static fn (array $group): array => $group['items'] ?? [])
            ->filter(static fn (array $item): bool => ($item['enabled'] ?? false) && is_string($item['route'] ?? null))
            ->take(9)
            ->values()
            ->all();

        return [
            'dashboardKind' => 'management',
            'dashboardTitle' => 'Panel de administración',
            'dashboardBodyClass' => 'modulo-dashboard modulo-admin-dashboard',
            'userName' => (string) $user->nombre,
            'dashboardNow' => $now,
            'products' => $activeProducts,
            'orders' => $ordersCount,
            'suppliers' => $suppliersCount,
            'salesThisMonth' => $salesThisMonth,
            'salesTrendPercent' => $this->porcentajeTendencia($salesThisMonth, $salesPreviousMonth),
            'ordersCount' => $ordersCount,
            'ordersThisMonth' => $ordersThisMonth,
            'ordersTrendPercent' => $this->porcentajeTendencia($ordersThisMonth, $ordersPreviousMonth),
            'activeProducts' => $activeProducts,
            'productsAddedThisMonth' => $productsAddedThisMonth,
            'lowStock' => $productosSinExistencias,
            'newCustomers' => $newCustomers,
            'newCustomersTrendPercent' => $this->porcentajeTendencia($newCustomers, $newCustomersPreviousMonth),
            'monthlySales' => $monthlySales,
            'monthlySalesBarHeights' => collect($monthlySales)->mapWithKeys(fn (array $month): array => [
                $month['key'] => $salesChartMax > 0 ? max(4, round(($month['value'] / $salesChartMax) * 100, 2)) : 4,
            ])->all(),
            'maxMonthlySales' => $maxMonthlySales,
            'salesChartMax' => $salesChartMax,
            'orderStatuses' => $orderStatuses,
            'orderStatusTotal' => $orderStatusTotal,
            'orderStatusChart' => $orderStatusChart,
            'recentOrders' => $recentOrders,
            'recentOrderProducts' => $recentOrders->mapWithKeys(static fn (Pedido $order): array => [
                $order->id => $order->details->first()?->product?->nombre,
            ])->all(),
            'lowStockProducts' => $productosSinExistenciasListado,
            'topProducts' => $topProducts,
            'campaigns' => $campaigns,
            'campaignMaxViews' => $campaignMaxViews,
            'campaignProgress' => $campaignProgress,
            'recentActivity' => $recentActivity,
            'reminders' => $reminders,
            'quickLinks' => $quickLinks,
        ];
    }

    /**
     * @return array<int, array{key:string, label:string, value:float}>
     */
    private function ventasMensuales(Collection $totales, Carbon $now): array
    {
        // Guarda en $start el resultado necesario para continuar esta operacion.
        $start = $now->copy()->startOfMonth()->subMonths(11);

        // Agrupa en $labels los datos que se utilizaran en la siguiente operacion.
        $labels = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];
        // Agrupa en $months los datos que se utilizaran en la siguiente operacion.
        $months = [];
        // Guarda en $cursor el resultado necesario para continuar esta operacion.
        $cursor = $start->copy();

        // Repite este bloque usando la configuracion del ciclo $index = 0; $index < 12; $index++.
        for ($index = 0; $index < 12; $index++) {
            // Guarda en $key el resultado necesario para continuar esta operacion.
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

    /**
     * Ejecuta la operación trendPercent definida por la clase DashboardController.
     *
     * @param  mixed  $current  Parámetro recibido por la operación.
     * @param  mixed  $previous  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function porcentajeTendencia(float|int $current, float|int $previous): ?float
    {
        // Comprueba la condicion (float) $previous === 0.0 antes de ejecutar este bloque.
        if ((float) $previous === 0.0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param  Collection<int, array{label:string, total:int, color:string}>  $statuses
     */
    private function graficoEstados(Collection $statuses, int $total): string
    {
        // Comprueba la condicion $total === 0 antes de ejecutar este bloque.
        if ($total === 0) {
            return 'conic-gradient(#dce9f6 0 100%)';
        }

        // Guarda en $start el resultado necesario para continuar esta operacion.
        $start = 0.0;
        // Agrupa en $stops los datos que se utilizaran en la siguiente operacion.
        $stops = [];

        // Recorre $statuses as $status y procesa cada elemento de la coleccion.
        foreach ($statuses as $status) {
            // Guarda en $end el resultado necesario para continuar esta operacion.
            $end = $start + (($status['total'] / $total) * 100);
            $stops[] = sprintf('%s %.2f%% %.2f%%', $status['color'], $start, $end);
            // Guarda en $start el resultado necesario para continuar esta operacion.
            $start = $end;
        }

        return 'conic-gradient('.implode(', ', $stops).')';
    }

    /**
     * @return Collection<int, array{icon:string, title:string, detail:string, tone:string}>
     */
    private function recordatorios(int $lowStock, int $pendingOrders, int $expiringCampaigns): Collection
    {
        // Guarda en $items el resultado necesario para continuar esta operacion.
        $items = collect();

        // Comprueba la condicion $lowStock > 0 antes de ejecutar este bloque.
        if ($lowStock > 0) {
            $items->push([
                'icon' => 'bi-exclamation-triangle',
                'title' => 'Revisar stock de productos',
                'detail' => $lowStock.' producto(s) con stock crítico',
                'tone' => 'danger',
            ]);
        }

        // Comprueba la condicion $pendingOrders > 0 antes de ejecutar este bloque.
        if ($pendingOrders > 0) {
            $items->push([
                'icon' => 'bi-clock-history',
                'title' => 'Procesar pedidos pendientes',
                'detail' => $pendingOrders.' pedido(s) en espera',
                'tone' => 'info',
            ]);
        }

        // Comprueba la condicion $expiringCampaigns > 0 antes de ejecutar este bloque.
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
