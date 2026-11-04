<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarEstadoPedidoRequest;
use App\Models\Comercio\Pedido;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioPedidos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function __construct(
        private readonly ServicioPedidos $orderService,
        private readonly ServicioAutorizacion $authorization,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($this->authorization->hasRole($user, 'administrador')
            && $this->authorization->allowsGlobal($user, 'gestion.pedidos.consultar')) {
            return $this->adminIndex($request);
        }

        $query = Pedido::with('details.product', 'user')->latest('creado_en')->latest('id');

        $query = $this->authorization->scopeRecordsFor($query, $request->user(), 'gestion.pedidos.consultar', 'usuario_id');

        $tiposCliente = [];
        if ($this->authorization->hasRole($user, 'ventas_minoristas')) {
            $tiposCliente[] = 'cliente_minorista';
        }
        if ($this->authorization->hasRole($user, 'ventas_mayoristas')) {
            $tiposCliente[] = 'cliente_mayorista';
        }

        if ($tiposCliente !== []) {
            $query->whereHas('user.rolesVigentes', fn (Builder $builder): Builder => $builder->whereIn('codigo', $tiposCliente));
        } elseif ($this->authorization->hasRole($user, 'cliente_minorista') || $this->authorization->hasRole($user, 'cliente_mayorista')) {
            $query->whereBelongsTo($request->user(), 'user');
        } else {
            abort(403);
        }

        return view('publico.comercio.pedidos', ['orders' => $query->paginate(20)]);
    }

    private function adminIndex(Request $request): View
    {
        $filters = $this->adminFilters($request);
        $orders = $this->adminOrderQuery($request, $filters)
            ->with([
                'user:id,nombre,correo,rol',
                'details.product:id,nombre,marca,url_imagen',
            ])
            ->latest('creado_en')
            ->latest('id')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $statusCounts = $this->scopedOrders($request)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $totalOrders = (int) $statusCounts->sum();
        $todayOrders = $this->scopedOrders($request)->whereDate('creado_en', today())->count();
        $yesterdayOrders = $this->scopedOrders($request)->whereDate('creado_en', today()->subDay())->count();
        $paidOrders = null;
        $pendingOrders = $this->countStatuses($statusCounts, ['Pendiente']);
        $shippingOrders = $this->countStatuses($statusCounts, ['Enviado']);
        $deliveredOrders = $this->countStatuses($statusCounts, ['Entregado']);
        $cancelledOrders = $this->countStatuses($statusCounts, ['Cancelado']);
        $statusSummary = $this->statusSummary($statusCounts, $totalOrders);
        $customerSummary = $this->customerSummary($request, $totalOrders);

        return view('publico.comercio.pedidos', [
            'orders' => $orders,
            'filters' => $filters,
            'totalOrders' => $totalOrders,
            'todayOrders' => $todayOrders,
            'todayOrdersTrend' => $this->trendPercent($todayOrders, $yesterdayOrders),
            'pendingOrders' => $pendingOrders,
            'paidOrders' => $paidOrders,
            'shippingOrders' => $shippingOrders,
            'deliveredOrders' => $deliveredOrders,
            'cancelledOrders' => $cancelledOrders,
            'statusSummary' => $statusSummary,
            'customerSummary' => $customerSummary,
            'recentOrders' => $this->scopedOrders($request)
                ->with(['user:id,nombre,correo', 'details.product:id,nombre,marca,url_imagen'])
                ->latest('creado_en')
                ->latest('id')
                ->limit(5)
                ->get(),
            'staleOrders' => $this->scopedOrders($request)
                ->whereNotIn('estado', ['Entregado', 'Cancelado'])
                ->where('creado_en', '<', now()->subHours(48))
                ->count(),
        ]);
    }

    public function show(Pedido $order): View
    {
        $order->loadMissing('user');
        $this->authorize('view', $order);

        return view('publico.comercio.pedido', ['order' => $order->load('details.product', 'user')]);
    }

    public function updateStatus(ActualizarEstadoPedidoRequest $request, Pedido $order): RedirectResponse
    {
        $order->loadMissing('user');
        $this->authorize('updateStatus', $order);
        $this->orderService->updateStatus($order, $request->user(), $request->validated('estado'), $request->ip());

        return back()->with('status', 'Estado de pedido actualizado.');
    }

    /**
     * @param  array{buscar:?string,estado:?string,periodo:string,canal:?string,por_pagina:int}  $filters
     * @return Builder<Pedido>
     */
    private function adminOrderQuery(Request $request, array $filters): Builder
    {
        return $this->scopedOrders($request)
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->when(ctype_digit($search), fn (Builder $query): Builder => $query->whereKey((int) $search))
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('nombre', 'like', '%'.$search.'%')->orWhere('correo', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($filters['estado'], fn (Builder $query, string $status): Builder => $query->where('estado', $status))
            ->when($filters['canal'] !== null, fn (Builder $query): Builder => $query->where('canal', $filters['canal']))
            ->when($filters['periodo'] !== 'all', fn (Builder $query): Builder => $query->where('creado_en', '>=', now()->subDays((int) $filters['periodo'] - 1)->startOfDay()));
    }

    /**
     * @return array{buscar:?string,estado:?string,periodo:string,canal:?string,por_pagina:int}
     */
    private function adminFilters(Request $request): array
    {
        $validatedChannel = $request->validate([
            'canal' => ['nullable', 'string', 'max:30'],
        ])['canal'] ?? null;
        $statusMap = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];
        $status = trim($request->string('estado')->toString());
        $period = trim($request->string('periodo')->toString());
        $perPage = $request->integer('por_pagina', 10);
        $search = trim($request->string('buscar')->toString());

        return [
            'buscar' => $search !== '' ? $search : null,
            'estado' => $statusMap[$status] ?? null,
            'periodo' => in_array($period, ['7', '30', '90', 'all'], true) ? $period : '30',
            'canal' => is_string($validatedChannel) && trim($validatedChannel) !== '' ? trim($validatedChannel) : null,
            'por_pagina' => in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
        ];
    }

    /**
     * @param  Collection<string, int|string>  $statusCounts
     * @param  array<int, string>  $statuses
     */
    private function countStatuses(Collection $statusCounts, array $statuses): int
    {
        return array_sum(array_map(static fn (string $status): int => (int) $statusCounts->get($status, 0), $statuses));
    }

    /**
     * @param  Collection<string, int|string>  $statusCounts
     * @return array<int, array{label:string,count:int,percent:float,color:string,key:string}>
     */
    private function statusSummary(Collection $statusCounts, int $totalOrders): array
    {
        $statuses = [
            ['key' => 'Pendiente', 'label' => 'Pendientes', 'color' => 'yellow'],
            ['key' => 'En proceso', 'label' => 'En preparación', 'color' => 'blue'],
            ['key' => 'Enviado', 'label' => 'Enviados', 'color' => 'purple'],
            ['key' => 'Entregado', 'label' => 'Entregados', 'color' => 'green'],
            ['key' => 'Cancelado', 'label' => 'Cancelados', 'color' => 'red'],
        ];

        return array_map(function (array $status) use ($statusCounts, $totalOrders): array {
            $count = (int) $statusCounts->get($status['key'], 0);

            return $status + [
                'count' => $count,
                'percent' => $totalOrders > 0 ? round(($count / $totalOrders) * 100, 1) : 0,
            ];
        }, $statuses);
    }

    /**
     * @return array<int, array{label:string,count:int,percent:float,color:string}>
     */
    private function customerSummary(Request $request, int $totalOrders): array
    {
        $minoristOrders = $this->scopedOrders($request)->whereHas('user.rolesVigentes', fn (Builder $query): Builder => $query->where('codigo', 'cliente_minorista'))->count();
        $mayoristOrders = $this->scopedOrders($request)->whereHas('user.rolesVigentes', fn (Builder $query): Builder => $query->where('codigo', 'cliente_mayorista'))->count();

        return array_map(
            static fn (string $label, int $count, string $color): array => [
                'label' => $label,
                'count' => $count,
                'percent' => $totalOrders > 0 ? round(($count / $totalOrders) * 100, 1) : 0,
                'color' => $color,
            ],
            ['Clientes minoristas', 'Clientes mayoristas'],
            [$minoristOrders, $mayoristOrders],
            ['blue', 'green'],
        );
    }

    /** @return Builder<Pedido> */
    private function scopedOrders(Request $request): Builder
    {
        return $this->authorization->scopeRecordsFor(Pedido::query(), $request->user(), 'gestion.pedidos.consultar', 'usuario_id');
    }

    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
