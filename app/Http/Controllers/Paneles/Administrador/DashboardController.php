<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Paneles\PanelDashboardController;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\DetallePedido;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Proveedor;
use App\Models\Marketing\Campania;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DashboardController extends PanelDashboardController
{
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

        $salesThisMonth = (float) Pedido::query()
            ->where('estado', '!=', 'Cancelado')
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->sum('total');
        $salesPreviousMonth = (float) Pedido::query()
            ->where('estado', '!=', 'Cancelado')
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->sum('total');

        $ordersCount = Pedido::query()->count();
        $ordersThisMonth = Pedido::query()
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $ordersPreviousMonth = Pedido::query()
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $activeProducts = Producto::query()->where('activo', true)->count();
        $productsAddedThisMonth = Producto::query()
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $lowStock = Producto::query()
            ->where('activo', true)
            ->where('existencias', '<=', 5)
            ->count();
        $suppliersCount = Proveedor::query()->where('activo', true)->count();

        $newCustomers = Cliente::query()
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $newCustomersPreviousMonth = Cliente::query()
            ->where('activo', true)
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $monthlySales = $this->monthlySales($now);
        $maxMonthlySales = max(1.0, (float) max(array_column($monthlySales, 'value')));
        $salesChartMax = max(5000.0, ceil($maxMonthlySales / 5000) * 5000);

        $statusColors = [
            'Pendiente' => '#2d83f5',
            'Pagado' => '#20c985',
            'Enviado' => '#8b5cf6',
            'En proceso' => '#f59e0b',
            'Cancelado' => '#ff4d5e',
        ];
        $orderStatuses = Pedido::query()
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

        $recentOrders = Pedido::query()
            ->with('user:id,nombre,correo')
            ->latest('creado_en')
            ->latest('id')
            ->limit(5)
            ->get();
        $lowStockProducts = Producto::query()
            ->where('activo', true)
            ->where('existencias', '<=', 5)
            ->orderBy('existencias')
            ->orderBy('nombre')
            ->limit(5)
            ->get(['id', 'nombre', 'marca', 'url_imagen', 'existencias']);
        $topProducts = DetallePedido::query()
            ->select([
                'producto_id',
                DB::raw('SUM(cantidad) as units_sold'),
                DB::raw('SUM(cantidad * precio_unitario) as revenue'),
            ])
            ->whereHas('order', static fn (Builder $query): Builder => $query->where('estado', '!=', 'Cancelado'))
            ->with('product:id,nombre,marca,url_imagen')
            ->groupBy('producto_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        $campaigns = Campania::query()
            ->where('activo', true)
            ->orderByDesc('vistas')
            ->limit(4)
            ->get(['id', 'nombre', 'titulo', 'vistas', 'clics', 'finaliza_en']);
        $campaignMaxViews = max(1, (int) $campaigns->max('vistas'));

        $recentActivity = RegistroAuditoria::query()
            ->with('user:id,nombre')
            ->latest('creado_en')
            ->limit(6)
            ->get(['id', 'usuario_id', 'accion', 'entidad', 'entidad_id', 'creado_en']);

        $pendingOrders = Pedido::query()
            ->whereIn('estado', ['Pendiente', 'En proceso'])
            ->count();
        $expiringCampaigns = Campania::query()
            ->where('activo', true)
            ->whereBetween('finaliza_en', [$now, $now->copy()->addDays(7)])
            ->count();
        $reminders = $this->reminders($lowStock, $pendingOrders, $expiringCampaigns);

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
            'lowStock' => $lowStock,
            'newCustomers' => $newCustomers,
            'newCustomersTrendPercent' => $this->trendPercent($newCustomers, $newCustomersPreviousMonth),
            'monthlySales' => $monthlySales,
            'maxMonthlySales' => $maxMonthlySales,
            'salesChartMax' => $salesChartMax,
            'orderStatuses' => $orderStatuses,
            'orderStatusTotal' => $orderStatusTotal,
            'orderStatusChart' => $orderStatusChart,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
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
    private function monthlySales(Carbon $now): array
    {
        $start = $now->copy()->startOfMonth()->subMonths(11);
        $totals = Pedido::query()
            ->where('estado', '!=', 'Cancelado')
            ->whereBetween('creado_en', [$start, $now->copy()->endOfMonth()])
            ->get(['total', 'creado_en'])
            ->groupBy(static fn (Pedido $order): string => $order->creado_en?->format('Y-m') ?? '')
            ->map(static fn (Collection $orders): float => (float) $orders->sum('total'));

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
                'value' => (float) ($totals[$key] ?? 0),
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
