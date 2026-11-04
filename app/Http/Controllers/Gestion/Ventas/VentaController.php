<?php

namespace App\Http\Controllers\Gestion\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarVentaRequest;
use App\Http\Requests\AnularVentaRequest;
use App\Http\Requests\FiltroVentaRequest;
use App\Http\Requests\GuardarVentaRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\Organizacion\Sede;
use App\Servicios\ConsultaVentas;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioVentas;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class VentaController extends Controller
{
    public function __construct(
        private readonly ConsultaVentas $ventas,
        private readonly ServicioVentas $registro,
        private readonly ServicioAutorizacion $autorizacion,
    ) {}

    public function index(FiltroVentaRequest $request): View
    {
        $filtros = $this->filters($request->validated());
        $query = $this->ventas->query($request->user(), $filtros);
        $estados = $this->ventas->query($request->user(), array_replace($filtros, ['estado' => null]))
            ->select('ventas.estado')->distinct()->orderBy('ventas.estado')->pluck('estado');
        $customers = collect();
        $products = collect();
        $canCreate = false;
        if (Schema::hasColumns('ventas', ['canal', 'observaciones']) && $this->autorizacion->allows($request->user(), 'gestion.ventas.crear')) {
            try {
                $ubicacion = $this->autorizacion->operationLocation($request->user());
                $canCreate = $ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null
                    && $this->autorizacion->allowsRecord($request->user(), 'gestion.ventas.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']);
            } catch (HttpExceptionInterface) {
                $canCreate = false;
            }
            if ($canCreate) {
                $customers = Cliente::query()->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->orderBy('razon_social')->orderBy('id')->get();
                $saldos = DB::table('existencias_almacen')->where('empresa_id', $ubicacion['empresa_id'])
                    ->where('sede_id', $ubicacion['sede_id'])->where('almacen_id', $ubicacion['almacen_id'])
                    ->selectRaw('producto_id, MIN(cantidad_fisica - cantidad_reservada) AS disponible')
                    ->groupBy('producto_id')->havingRaw('COUNT(*) = 1')->get()->keyBy('producto_id');
                $products = Producto::query()->active()->where('empresa_id', $ubicacion['empresa_id'])
                    ->whereIn('id', $saldos->keys())->orderBy('nombre')->get();
                $products->each(static function (Producto $producto) use ($saldos): void {
                    $producto->existencias = max(0, (int) floor((float) $saldos->get($producto->id)->disponible));
                });
                if (! is_string($request->session()->get('venta_token'))) {
                    $request->session()->put('venta_token', bin2hex(random_bytes(32)));
                }
            }
        }

        $orders = (clone $query)->with(['cliente', 'pedido:id,canal', 'detalles'])->withCount('detalles')
            ->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->paginate(10)->withQueryString();
        $cancelables = $this->autorizacion->scopeRecordsFor(
            Venta::query()->whereIn('ventas.id', $orders->getCollection()->modelKeys()), $request->user(), 'gestion.ventas.anular',
        )->pluck('ventas.id')->all();
        $filtrosDisponibles = array_replace($filtros, ['cliente_id' => null, 'sede_id' => null]);
        $clientesDisponibles = Cliente::query()->whereIn('id', $this->ventas->query($request->user(), $filtrosDisponibles)->select('ventas.cliente_id'))
            ->orderBy('razon_social')->orderBy('id')->get(['id', 'razon_social', 'nombre_contacto']);
        $sedesDisponibles = Sede::query()->whereIn('id', $this->ventas->query($request->user(), $filtrosDisponibles)->select('ventas.sede_id'))
            ->orderBy('nombre')->orderBy('id')->get(['id', 'nombre']);

        return view('gestion.ventas.listar', [
            ...$this->ventas->resumen($request->user(), $filtros),
            'filters' => $filtros,
            'estados' => $estados,
            'orders' => $orders,
            'cancelables' => $cancelables,
            'clientesDisponibles' => $clientesDisponibles,
            'sedesDisponibles' => $sedesDisponibles,
            'latestSales' => (clone $query)->with('cliente')->orderByDesc('ventas.fecha_venta')->orderByDesc('ventas.id')->limit(5)->get(),
            'customers' => $customers,
            'products' => $products,
            'canCreate' => $canCreate,
            'editOrder' => null,
        ]);
    }

    public function store(GuardarVentaRequest $request): RedirectResponse
    {
        abort_unless(Schema::hasColumns('ventas', ['canal', 'observaciones']), 503, 'La migración de canal y observaciones está pendiente de autorización y aplicación.');
        $datos = $request->validated();
        $venta = $this->registro->registrarFisica($datos, $request->user(), $datos['venta_token'], $request->ip());
        $request->session()->forget('venta_token');

        return redirect()->route('management.sales')->with('status', 'Venta '.$venta->codigo.' confirmada. No se registró ningún pago.');
    }

    public function update(ActualizarVentaRequest $request, Pedido $order): RedirectResponse
    {
        abort(503, 'La edición de ventas está pendiente de integración con ventas y sus detalles.');
    }

    public function cancel(AnularVentaRequest $request, Venta $venta): RedirectResponse
    {
        $this->registro->anular($venta, $request->user(), $request->validated('motivo'), $request->ip());

        return redirect()->route('management.sales')->with('status', 'La venta está anulada.');
    }

    public function export(FiltroVentaRequest $request): StreamedResponse
    {
        $ventas = $this->ventas->query($request->user(), $this->filters($request->validated()))
            ->with(['cliente:id,razon_social,nombre_contacto', 'pedido:id,canal'])
            ->orderBy('ventas.fecha_venta')->orderBy('ventas.id')->lazy(500);

        return response()->streamDownload(function () use ($ventas): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['codigo', 'fecha_venta', 'cliente', 'total', 'moneda', 'estado', 'canal']);
            foreach ($ventas as $venta) {
                $safe = static function (?string $value): string {
                    $value ??= '';

                    return preg_match('/^[\s\x00-\x1f]*[=+\-@]/u', $value) ? "'".$value : $value;
                };
                fputcsv($output, [
                    $safe($venta->codigo), $venta->fecha_venta?->toDateTimeString(),
                    $safe($venta->cliente?->razon_social ?: $venta->cliente?->nombre_contacto),
                    $venta->total, $safe($venta->moneda), $safe($venta->estado), $safe($venta->canal ?: $venta->pedido?->canal),
                ]);
            }
            fclose($output);
        }, 'ventas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(array $input): array
    {
        $today = CarbonImmutable::today();
        $to = $this->parseDate((string) ($input['hasta'] ?? ''), $today);
        $defaultFrom = filled($input['hasta'] ?? null) ? $to->subDays(29) : $today->subDays(29);
        $from = $this->parseDate((string) ($input['desde'] ?? ''), $defaultFrom);
        if ($from->greaterThan($to) && filled($input['desde'] ?? null)) {
            $to = $from;
        }
        $canal = (string) ($input['canal'] ?? '');

        return [
            'buscar' => mb_substr(trim((string) ($input['buscar'] ?? '')), 0, 120) ?: null,
            'estado' => mb_substr(trim((string) ($input['estado'] ?? '')), 0, 30) ?: null,
            'canal' => in_array($canal, ['ecommerce', 'presencial'], true) ? $canal : null,
            'cliente_id' => (int) ($input['cliente_id'] ?? 0) > 0 ? (int) $input['cliente_id'] : null,
            'sede_id' => (int) ($input['sede_id'] ?? 0) > 0 ? (int) $input['sede_id'] : null,
            'desde' => $from->toDateString(), 'hasta' => $to->toDateString(),
            'from' => $from->startOfDay(), 'to' => $to->endOfDay(),
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
}
