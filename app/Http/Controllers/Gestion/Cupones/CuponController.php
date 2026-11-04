<?php

namespace App\Http\Controllers\Gestion\Cupones;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCuponRequest;
use App\Http\Requests\DesactivarCuponRequest;
use App\Http\Requests\GuardarCuponRequest;
use App\Models\Comercio\Cupon;
use App\Servicios\ServicioCupones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CuponController extends Controller
{
    public function index(Request $request, ServicioCupones $coupons): View
    {
        $filters = $this->filters($request);
        $couponList = $coupons->query($filters)->orderByDesc('id')->paginate(10)->withQueryString();
        $couponList->setCollection($couponList->getCollection()->map(function (Cupon $coupon) use ($coupons): Cupon {
            $coupon->setAttribute('estado_vigencia', $coupons->status($coupon));

            return $coupon;
        }));

        return view('gestion.cupones.listar', [
            'coupons' => $couponList,
            'filters' => $filters,
            ...$coupons->dashboard(),
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

    public function export(Request $request, ServicioCupones $coupons): StreamedResponse
    {
        $records = $coupons->query($this->filters($request))->orderBy('id')->lazy(500);

        return response()->streamDownload(function () use ($records, $coupons): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Código', 'Tipo de descuento', 'Valor', 'Compra mínima', 'Usos', 'Límite de usos', 'Vigente desde', 'Vigente hasta', 'Estado']);
            foreach ($records as $coupon) {
                fputcsv($output, [
                    $this->safeCsvValue($coupon->codigo),
                    $this->safeCsvValue($coupon->tipo_descuento),
                    $coupon->valor,
                    $coupon->minimo_compra,
                    $coupon->usos,
                    $coupon->limite_usos,
                    $coupon->inicia_en?->toDateTimeString(),
                    $coupon->finaliza_en?->toDateTimeString(),
                    $coupons->status($coupon),
                ]);
            }

            fclose($output);
        }, 'cupones.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
