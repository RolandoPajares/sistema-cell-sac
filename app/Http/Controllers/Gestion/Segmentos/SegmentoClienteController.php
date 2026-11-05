<?php

namespace App\Http\Controllers\Gestion\Segmentos;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarSegmentoClienteRequest;
use App\Models\Marketing\SegmentoCliente;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SegmentoClienteController extends Controller
{
    public function index(): View
    {
        return view('gestion.segmentos.listar', [
            'segments' => SegmentoCliente::query()->orderBy('nombre')->paginate(30),
            'roles' => DB::table('roles')->where('activo', true)->orderBy('nombre')->pluck('codigo'),
        ]);
    }

    public function store(GuardarSegmentoClienteRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $segment = SegmentoCliente::create([
            'nombre' => $data['nombre'],
            'tipo' => 'roles',
            'criterios' => ['roles' => array_values(array_unique($data['roles']))],
            'activo' => true,
        ]);
        $audit->record($request->user()->id, 'segment.created', 'segmentos_clientes', (int) $segment->id, null, ['nombre' => $segment->nombre, 'criterios' => $segment->criterios], $request->ip());

        return back()->with('status', 'Segmento creado. Sus campaÃ±as se mostrarÃ¡n a los roles seleccionados.');
    }
}
