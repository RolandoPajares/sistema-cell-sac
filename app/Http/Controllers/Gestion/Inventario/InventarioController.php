<?php

namespace App\Http\Controllers\Gestion\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\AjustarInventarioRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function __construct(private readonly ServicioInventario $inventoryService) {}

    public function index(): View
    {
        return view('gestion.inventario.listar', ['products' => Producto::query()->with(['category', 'suppliers'])->orderBy('nombre')->paginate(30)]);
    }

    public function adjust(AjustarInventarioRequest $request, Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $before = (int) $product->existencias;
        $this->inventoryService->adjust($product, $data['tipo_movimiento'], (int) $data['cantidad'], $data['notas'], $request->user());
        $audit->record($request->user()->id, 'inventory.adjusted', 'productos', $product->id, ['existencias' => $before], ['existencias' => $product->fresh()->existencias], $request->ip());

        return back()->with('status', 'Movimiento de inventario registrado.');
    }

    public function adjustLegacy(AjustarInventarioRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $product = Producto::findOrFail((int) $request->input('producto_id'));

        return $this->adjust($request, $product, $audit);
    }
}
