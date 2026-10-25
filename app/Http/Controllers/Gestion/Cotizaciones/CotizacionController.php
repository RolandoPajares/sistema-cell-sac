<?php

namespace App\Http\Controllers\Gestion\Cotizaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarCotizacionRequest;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Servicios\ServicioCotizaciones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    public function __construct(private readonly ServicioCotizaciones $quotationService) {}

    public function index(Request $request): View
    {
        $query = Cotizacion::with('customer', 'details.product')->latest('creado_en')->latest('id');
        if ($request->user()->rol === 'cliente_mayorista') {
            $query->whereHas('customer', fn ($builder) => $builder->where('usuario_id', $request->user()->id));
        }

        return view('gestion.cotizaciones.listar', [
            'quotations' => $query->paginate(20),
            'customers' => Cliente::query()->where('tipo', 'mayorista')->where('activo', true)->orderBy('nombre_contacto')->get(),
            'products' => Producto::query()->active()->orderBy('nombre')->get(),
        ]);
    }

    public function store(GuardarCotizacionRequest $request): RedirectResponse
    {
        $this->quotationService->create($request->validated(), $request->user());

        return back()->with('status', 'CotizaciÃ³n guardada como borrador; aÃºn no se envÃ­a al cliente.');
    }
}
