<?php

namespace App\Http\Controllers\Gestion\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarVentaRequest;
use App\Http\Requests\GuardarVentaRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\DetallePedido;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\ServicioVentas;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VentaController extends Controller
{
    public function __construct(private readonly ServicioVentas $salesService) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $ordersQuery = $this->orderQuery($filters);
        $salesQuery = $this->salesQuery($filters);
        $summary = (clone $salesQuery)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as realized_sales')
            ->first();
        $totalSales = (float) ($summary?->total_sales ?? 0);
        $realizedSales = (int) ($summary?->realized_sales ?? 0);
        $ordersCount = (clone $ordersQuery)->count();
        $customersServed = (int) (clone $ordersQuery)
            ->whereNotNull('usuario_id')
            ->distinct()
            ->count('usuario_id');
        $orders = (clone $ordersQuery)
            ->with([
                'user:id,nombre,correo',
                'details.product:id,nombre,url_imagen',
            ])
            ->withCount('details')
            ->latest('creado_en')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
        $editOrder = null;
        $editOrderId = $request->integer('editar');

        if ($editOrderId > 0) {
            $editOrder = Pedido::query()
                ->with(['user:id,nombre,correo', 'details.product:id,nombre,precio,precio_oferta,existencias,url_imagen'])
                ->find($editOrderId);
        }

        return view('gestion.ventas.listar', [
            'filters' => $filters,
            'orders' => $orders,
            'totalSales' => $totalSales,
            'realizedSales' => $realizedSales,
            'customersServed' => $customersServed,
            'averageTicket' => $realizedSales > 0 ? $totalSales / $realizedSales : 0,
            'ordersCount' => $ordersCount,
            'salesTrend' => $this->salesTrend($filters),
            'channelSummary' => $this->channelSummary($ordersCount),
            'latestSales' => (clone $ordersQuery)
                ->with('user:id,nombre,correo')
                ->latest('creado_en')
                ->latest('id')
                ->limit(5)
                ->get(),
            'topProducts' => $this->topProducts($filters),
            'customers' => User::query()
                ->whereIn('rol', ['cliente_minorista', 'cliente_mayorista'])
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'correo', 'rol']),
            'products' => Producto::query()
                ->active()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'marca', 'precio', 'precio_oferta', 'existencias', 'url_imagen']),
            'editOrder' => $editOrder,
        ]);
    }

    public function store(GuardarVentaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $customer = User::query()
            ->whereIn('rol', ['cliente_minorista', 'cliente_mayorista'])
            ->findOrFail($validated['cliente_id']);

        $this->salesService->register(
            $customer,
            $validated['items'],
            $request->user(),
            $validated['tipo_venta'],
            $validated['fecha_venta'],
            $validated['observaciones'] ?? null,
            $request->ip(),
        );

        return redirect()->route('management.sales')->with('status', 'Venta registrada correctamente.');
    }

    public function update(ActualizarVentaRequest $request, Pedido $order): RedirectResponse
    {
        $validated = $request->validated();
        $customer = User::query()
            ->whereIn('rol', ['cliente_minorista', 'cliente_mayorista'])
            ->findOrFail($validated['cliente_id']);

        $this->salesService->update(
            $order,
            $customer,
            $validated['items'],
            $request->user(),
            $validated['tipo_venta'],
            $validated['fecha_venta'],
            $validated['observaciones'] ?? null,
            $request->ip(),
        );

        return redirect()->route('management.sales')->with('status', 'Venta actualizada correctamente.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $orders = $this->orderQuery($filters)
            ->select(['id', 'usuario_id', 'total', 'estado', 'creado_en'])
            ->orderBy('creado_en')
            ->orderBy('id')
            ->cursor();

        return response()->streamDownload(function () use ($orders): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['id', 'usuario_id', 'total', 'estado', 'fecha', 'canal']);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->id,
                    $order->usuario_id,
                    $order->total,
                    $order->estado,
                    $order->creado_en?->toDateTimeString(),
                    'Tienda online',
                ]);
            }

            fclose($output);
        }, 'ventas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array{buscar:?string,estado:?string,canal:?string,desde:string,hasta:string,from:CarbonImmutable,to:CarbonImmutable}  $filters
     * @return Builder<Pedido>
     */
    private function orderQuery(array $filters): Builder
    {
        return $this->applyOrderFilters(Pedido::query(), $filters);
    }

    /**
     * @param  array{buscar:?string,estado:?string,canal:?string,desde:string,hasta:string,from:CarbonImmutable,to:CarbonImmutable}  $filters
     * @return Builder<Pedido>
     */
    private function salesQuery(array $filters): Builder
    {
        return $this->applyOrderFilters(Pedido::query(), $filters)
            ->whereNotIn('estado', ['Cancelado', 'Cancelada']);
    }

    /**
     * @param  Builder<Pedido>  $query
     * @param  array{buscar:?string,estado:?string,canal:?string,desde:string,hasta:string,from:CarbonImmutable,to:CarbonImmutable}  $filters
     * @return Builder<Pedido>
     */
    private function applyOrderFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->when(ctype_digit($search), fn (Builder $query): Builder => $query->whereKey((int) $search))
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('nombre', 'like', '%'.$search.'%')
                                ->orWhere('correo', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($filters['estado'], fn (Builder $query, string $status): Builder => $query->where('estado', $status))
            ->whereBetween('creado_en', [$filters['from'], $filters['to']]);
    }

    /**
     * @return array{buscar:?string,estado:?string,canal:?string,desde:string,hasta:string,from:CarbonImmutable,to:CarbonImmutable}
     */
    private function filters(Request $request): array
    {
        $today = CarbonImmutable::today();
        $defaultFrom = $today->subDays(29);
        $from = $this->parseDate($request->string('desde')->toString(), $defaultFrom);
        $to = $this->parseDate($request->string('hasta')->toString(), $today);

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $statusMap = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];
        $status = $request->string('estado')->toString();
        $search = trim($request->string('buscar')->toString());
        $channel = $request->string('canal')->toString();

        return [
            'buscar' => $search !== '' ? $search : null,
            'estado' => $statusMap[$status] ?? null,
            'canal' => $channel === 'tienda_online' ? 'tienda_online' : null,
            'desde' => $from->toDateString(),
            'hasta' => $to->toDateString(),
            'from' => $from->startOfDay(),
            'to' => $to->endOfDay(),
        ];
    }

    private function parseDate(string $value, CarbonImmutable $fallback): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $fallback;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return $fallback;
        }

        return $date instanceof CarbonImmutable && $date->format('Y-m-d') === $value ? $date : $fallback;
    }

    /**
     * @param  array{buscar:?string,estado:?string,canal:?string,desde:string,hasta:string,from:CarbonImmutable,to:CarbonImmutable}  $filters
     * @return array<int, array{label:string,sales:float,orders:int,sales_height:float,orders_height:float}>
     */
    private function salesTrend(array $filters): array
    {
        $rows = $this->salesQuery($filters)
            ->selectRaw('DATE(creado_en) as day, COALESCE(SUM(total), 0) as sales, COUNT(*) as orders')
            ->groupByRaw('DATE(creado_en)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');
        $days = $filters['from']->startOfDay()->diffInDays($filters['to']->startOfDay()) + 1;
        $points = [];

        for ($index = 0; $index < min($days, 31); $index++) {
            $date = $filters['from']->startOfDay()->addDays($index);
            $row = $rows->get($date->toDateString());
            $points[] = [
                'label' => $date->format('d M'),
                'sales' => (float) ($row?->sales ?? 0),
                'orders' => (int) ($row?->orders ?? 0),
            ];
        }

        if ($days > 31) {
            $points = $rows->map(static function (object $row): array {
                $date = CarbonImmutable::parse((string) $row->day);

                return [
                    'label' => $date->format('d M'),
                    'sales' => (float) $row->sales,
                    'orders' => (int) $row->orders,
                ];
            })->values()->all();
        }

        $maxSales = max(1, ...array_column($points, 'sales'));
        $maxOrders = max(1, ...array_column($points, 'orders'));

        return array_map(static function (array $point) use ($maxSales, $maxOrders): array {
            return $point + [
                'sales_height' => $point['sales'] > 0 ? max(6, round(($point['sales'] / $maxSales) * 100, 2)) : 0,
                'orders_height' => $point['orders'] > 0 ? max(6, round(($point['orders'] / $maxOrders) * 100, 2)) : 0,
            ];
        }, $points);
    }

    /**
     * @return array<int, array{label:string,count:int,percent:float,color:string}>
     */
    private function channelSummary(int $ordersCount): array
    {
        return [[
            'label' => 'Tienda online',
            'count' => $ordersCount,
            'percent' => $ordersCount > 0 ? 100.0 : 0.0,
            'color' => '#1784f5',
        ]];
    }

    /**
     * @param  array{buscar:?string,estado:?string,canal:?string,desde:string,hasta:string,from:CarbonImmutable,to:CarbonImmutable}  $filters
     * @return array<int, array{name:string,units:int,revenue:float,image:?string}>
     */
    private function topProducts(array $filters): array
    {
        $details = DetallePedido::query()
            ->whereHas('order', function (Builder $query) use ($filters): void {
                $this->applyOrderFilters($query->whereNotIn('estado', ['Cancelado', 'Cancelada']), $filters);
            })
            ->with('product:id,nombre,url_imagen')
            ->get();

        return $details
            ->groupBy('producto_id')
            ->map(static function (Collection $productDetails): array {
                $product = $productDetails->first()?->product;

                return [
                    'name' => $product?->nombre ?? 'Producto',
                    'units' => (int) $productDetails->sum(static fn (DetallePedido $detail): int => (int) $detail->cantidad),
                    'revenue' => (float) $productDetails->sum(static fn (DetallePedido $detail): float => (float) $detail->cantidad * (float) $detail->precio_unitario),
                    'image' => $product?->url_imagen,
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values()
            ->all();
    }
}
