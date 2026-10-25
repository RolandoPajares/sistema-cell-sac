<?php

namespace App\Http\Controllers\Gestion\Cupones;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCuponRequest;
use App\Models\Comercio\Cupon;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CuponController extends Controller
{
    public function index(): View
    {
        return view('gestion.cupones.listar', ['coupons' => Cupon::query()->latest()->paginate(30)]);
    }

    public function store(GuardarCuponRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        abort_if($data['tipo_descuento'] === 'porcentaje' && $data['valor'] > 100, 422, 'El porcentaje no puede superar 100.');
        $data['codigo'] = mb_strtoupper($data['codigo']);
        $coupon = Cupon::create($data + ['activo' => true, 'usos' => 0]);
        $audit->record($request->user()->id, 'coupon.created', 'cupones', (int) $coupon->id, null, $coupon->only(['codigo', 'tipo_descuento', 'valor', 'minimo_compra']), $request->ip());

        return back()->with('status', 'CupÃ³n registrado.');
    }
}
