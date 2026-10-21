<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustInventoryRequest;
use App\Models\Product;
use App\Services\AuditLogger;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $productsQuery = Product::query()
            ->with(['category', 'suppliers']);

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%'.$search.'%')
                    ->orWhere('marca', 'like', '%'.$search.'%');
            });
        }

        $products = $productsQuery
            ->orderBy('nombre')
            ->paginate(30)
            ->withQueryString();

        return view('management.inventory', [
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function adjust(
        AdjustInventoryRequest $request,
        Product $product,
        AuditLogger $audit
    ): RedirectResponse {
        $data = $request->validated();

        $before = (int) $product->existencias;

        $this->inventoryService->adjust(
            $product,
            $data['tipo_movimiento'],
            (int) $data['cantidad'],
            $data['notas'],
            $request->user()
        );

        $audit->record(
            $request->user()->id,
            'inventory.adjusted',
            'productos',
            $product->id,
            ['existencias' => $before],
            ['existencias' => $product->fresh()->existencias],
            $request->ip()
        );

        return back()->with(
            'status',
            'Movimiento de inventario registrado.'
        );
    }

    public function adjustLegacy(
        AdjustInventoryRequest $request,
        AuditLogger $audit
    ): RedirectResponse {
        $product = Product::findOrFail(
            (int) $request->input('producto_id')
        );

        return $this->adjust($request, $product, $audit);
    }
}
