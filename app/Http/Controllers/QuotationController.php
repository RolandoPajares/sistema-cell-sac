<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuotationRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function __construct(private readonly QuotationService $quotationService) {}

    public function index(Request $request): View
    {
        $query = Quotation::with('customer', 'details.product')->latest('creado_en')->latest('id');
        if ($request->user()->rol === 'cliente_mayorista') {
            $query->whereHas('customer', fn ($builder) => $builder->where('usuario_id', $request->user()->id));
        }

        return view('management.quotations', [
            'quotations' => $query->paginate(20),
            'customers' => Customer::query()->where('tipo', 'mayorista')->where('activo', true)->orderBy('nombre_contacto')->get(),
            'products' => Product::query()->active()->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        $this->quotationService->create($request->validated(), $request->user());

        return back()->with('status', 'Cotización guardada como borrador; aún no se envía al cliente.');
    }
}
