<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerSegmentRequest;
use App\Models\CustomerSegment;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerSegmentController extends Controller
{
    public function index(): View
    {
        return view('management.segments', ['segments' => CustomerSegment::query()->orderBy('nombre')->paginate(30), 'roles' => User::ROLES]);
    }

    public function store(StoreCustomerSegmentRequest $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validated();
        $segment = CustomerSegment::create([
            'nombre' => $data['nombre'],
            'tipo' => 'roles',
            'criterios' => ['roles' => array_values(array_unique($data['roles']))],
            'activo' => true,
        ]);
        $audit->record($request->user()->id, 'segment.created', 'segmentos_clientes', (int) $segment->id, null, ['nombre' => $segment->nombre, 'criterios' => $segment->criterios], $request->ip());

        return back()->with('status', 'Segmento creado. Sus campañas se mostrarán a los roles seleccionados.');
    }
}
