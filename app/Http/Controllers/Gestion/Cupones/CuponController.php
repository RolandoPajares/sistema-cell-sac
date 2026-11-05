<?php

namespace App\Http\Controllers\Gestion\Cupones;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCuponRequest;
use App\Http\Requests\DesactivarCuponRequest;
use App\Http\Requests\GuardarCuponRequest;
use App\Models\Comercio\Cupon;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCupones;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CuponController extends Controller
{
    public function index(Request $request, ServicioCupones $coupons, ServicioAutorizacion $autorizacion): View
    {
        $filters = $this->filters($request);
        $empresaId = $autorizacion->activeCompanyId();
        $couponList = $coupons->query($filters, $empresaId)->orderByDesc('id')->paginate(10)->withQueryString();
        $couponList->setCollection($couponList->getCollection()->map(function (Cupon $coupon) use ($coupons): Cupon {
            $coupon->setAttribute('estado_vigencia', $coupons->status($coupon));

            return $coupon;
        }));

        return view('gestion.cupones.listar', [
            'coupons' => $couponList,
            'filters' => $filters,
            ...$coupons->dashboard($empresaId),
            'empresaCuponDisponible' => $coupons->empresaDisponible(),
        ]);
    }

    public function store(GuardarCuponRequest $request, ServicioCupones $coupons): RedirectResponse
    {
        $coupons->register($request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'Cupón registrado.');
    }

    public function update(ActualizarCuponRequest $request, Cupon $coupon, ServicioCupones $coupons): RedirectResponse
    {
        $coupons->update($coupon, $request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'Cupón actualizado.');
    }

    public function deactivate(DesactivarCuponRequest $request, Cupon $coupon, ServicioCupones $coupons): RedirectResponse
    {
        $coupons->deactivate($coupon, $request->user(), $request->ip());

        return back()->with('status', 'Cupón cancelado. Ya no estará disponible para nuevos canjes.');
    }

    public function export(Request $request, ServicioCupones $coupons, ServicioAutorizacion $autorizacion, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        $format = (string) ($request->route('format') ?: 'csv');
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $filters = $this->filters($request);
        $records = $coupons->query($filters, $autorizacion->activeCompanyId())->orderBy('id')->limit(5000)->get();
        $rows = $records->map(fn (Cupon $coupon): array => [
            $this->safeCsvValue($coupon->codigo),
            $this->safeCsvValue($coupon->tipo_descuento),
            $coupon->valor,
            $coupon->minimo_compra,
            $coupon->usos,
            $coupon->limite_usos,
            $coupon->inicia_en?->toDateTimeString(),
            $coupon->finaliza_en?->toDateTimeString(),
            $coupons->status($coupon),
        ])->all();
        $response = $exporter->download($format, 'cupones-'.now()->format('Ymd'),
            ['Código', 'Tipo de descuento', 'Valor', 'Compra mínima', 'Usos', 'Límite de usos', 'Vigente desde', 'Vigente hasta', 'Estado'],
            $rows,
            [
                'title' => 'REPORTE DE CUPONES',
                'subtitle' => 'Cupones reales consultados desde MariaDB.',
                'module' => 'Ecommerce y Marketing',
                'report_name' => 'Cupones',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa activa y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Cupones incluidos', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'cupones', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /** @return array{buscar:?string,tipo:?string,estado:?string,vigencia:?string} */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'in:porcentaje,fijo'],
            'estado' => ['nullable', 'in:Activo,Programado,Finalizado'],
            'vigencia' => ['nullable', 'in:este_mes,proximos_30_dias,historico'],
        ]);
        $search = trim((string) ($validated['buscar'] ?? ''));

        return [
            'buscar' => $search !== '' ? $search : null,
            'tipo' => $validated['tipo'] ?? null,
            'estado' => $validated['estado'] ?? null,
            'vigencia' => $validated['vigencia'] ?? null,
        ];
    }

    private function safeCsvValue(string $value): string
    {
        return preg_match('/^[\s\x00-\x1f]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}
