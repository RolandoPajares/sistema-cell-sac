<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCouponRequest;
use App\Models\Coupon;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('management.coupons', ['coupons' => Coupon::query()->latest()->paginate(30)]);
    }

    public function store(StoreCouponRequest $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validated();
        abort_if($data['tipo_descuento'] === 'porcentaje' && $data['valor'] > 100, 422, 'El porcentaje no puede superar 100.');
        $data['codigo'] = mb_strtoupper($data['codigo']);
        $coupon = Coupon::create($data + ['activo' => true, 'usos' => 0]);
        $audit->record($request->user()->id, 'coupon.created', 'cupones', (int) $coupon->id, null, $coupon->only(['codigo', 'tipo_descuento', 'valor', 'minimo_compra']), $request->ip());

        return back()->with('status', 'Cupón registrado.');
    }
}
