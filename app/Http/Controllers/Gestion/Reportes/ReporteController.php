<?php

namespace App\Http\Controllers\Gestion\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltroReporteRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Marketing\Campania;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(FiltroReporteRequest $request): View
    {
        $campaigns = Campania::query()->selectRaw('COALESCE(SUM(vistas), 0) as views, COALESCE(SUM(clics), 0) as clicks')->first();
        $filters = $request->validated();
        $ordersQuery = $this->salesQuery($filters);
        $movementsQuery = $this->applyDateFilters(
            MovimientoInventario::query()->with('product')->latest('creado_en'),
            $filters,
        );

        return view('gestion.reportes.listar', [
            'sales' => $ordersQuery->sum('total'),
            'orders' => (clone $ordersQuery)->count(),
            'products' => Producto::count(),
            'lowStock' => Producto::where('existencias', '<=', 5)->count(),
            'campaigns' => $campaigns ?? (object) ['views' => 0, 'clicks' => 0],
            'movements' => $movementsQuery->limit(20)->get(),
        ]);
    }

    public function export(FiltroReporteRequest $request): StreamedResponse
    {
        $orders = $this->salesQuery($request->validated())
            ->select(['id', 'usuario_id', 'subtotal', 'descuento', 'total', 'estado', 'creado_en'])
            ->orderBy('creado_en')
            ->orderBy('id')
            ->cursor();

        return response()->streamDownload(function () use ($orders): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['id', 'usuario_id', 'subtotal', 'descuento', 'total', 'estado', 'creado_en']);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->id,
                    $order->usuario_id,
                    $order->subtotal,
                    $order->descuento,
                    $order->total,
                    $order->estado,
                    $order->creado_en?->toDateTimeString(),
                ]);
            }

            fclose($output);
        }, 'reporte-pedidos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Pedido>
     */
    private function salesQuery(array $filters): Builder
    {
        return $this->applyDateFilters(
            Pedido::query()->whereNotIn('estado', ['Cancelado', 'Cancelada']),
            $filters,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyDateFilters(Builder $query, array $filters): Builder
    {
        foreach (['desde' => '>=', 'hasta' => '<='] as $field => $operator) {
            if (! isset($filters[$field])) {
                continue;
            }

            $query->whereDate('creado_en', $operator, $filters[$field]);
        }

        return $query;
    }
}
