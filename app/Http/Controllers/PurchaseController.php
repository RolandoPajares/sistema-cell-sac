<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $purchaseService) {}

    public function index(): View
    {
        return view('management.purchases', [
            'purchases' => Purchase::with('supplier')->latest('fecha_compra')->latest('id')->paginate(30),
            'suppliers' => Supplier::query()->where('activo', true)->orderBy('nombre')->get(),
            'products' => Product::query()->active()->orderBy('nombre')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $this->purchaseService->receive($request->validated(), $request->user(), $request->ip());

        return back()->with('status', 'Compra recibida e inventario actualizado.');
    }
}
