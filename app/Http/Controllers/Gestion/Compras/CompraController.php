<?php

namespace App\Http\Controllers\Gestion\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCompraRequest;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Servicios\ServicioCompras;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function __construct(private readonly ServicioCompras $purchaseService) {}

    public function index(): View
    {
        return view('gestion.compras.listar', [
            'purchases' => Compra::with('supplier')->latest('fecha_compra')->latest('id')->paginate(30),
            'suppliers' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'products' => Producto::query()->active()->orderBy('nombre')->get(),
        ]);
    }

    public function store(GuardarCompraRequest $request): RedirectResponse
    {
        $this->purchaseService->receive($request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'Compra recibida e inventario actualizado.');
    }
}
