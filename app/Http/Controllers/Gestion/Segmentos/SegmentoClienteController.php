<?php

namespace App\Http\Controllers\Gestion\Segmentos;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarSegmentoClienteRequest;
use App\Http\Requests\ActualizarSegmentoClienteRequest;
use App\Models\Marketing\SegmentoCliente;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SegmentoClienteController extends Controller
{
    public function index(): View
    {
        $search = trim((string) request('buscar', ''));
        $tipo = request('tipo');
        $estado = request('estado');
        $query = SegmentoCliente::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nombre', 'like', "%{$search}%")
                    ->orWhere('tipo', 'like', "%{$search}%");
            });
        }
        if ($tipo === 'roles') {
            $query->where('tipo', $tipo);
        }
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('activo', $estado === 'activo');
        }

        $allSegments = SegmentoCliente::query();
        return view('gestion.segmentos.listar', [
            'segments' => $query->paginate(30)->withQueryString(),
            'activeSegments' => (clone $allSegments)->where('activo', true)->count(),
            'dynamicSegments' => (clone $allSegments)->where('tipo', '!=', 'roles')->count(),
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

    public function update(ActualizarSegmentoClienteRequest $request, SegmentoCliente $segment, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $segment->getAttributes();
        $data = $request->validated();
        $segment->update([
            'nombre' => $data['nombre'],
            'criterios' => ['roles' => array_values(array_unique($data['roles']))],
            'activo' => (bool) ($data['activo'] ?? $segment->activo),
        ]);
        $audit->record($request->user()->id, 'segment.updated', 'segmentos_clientes', (int) $segment->id, $before, $segment->fresh()->getAttributes(), $request->ip());

        return back()->with('status', 'Segmento actualizado correctamente.');
    }

    public function deactivate(Request $request, SegmentoCliente $segment, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $segment->getAttributes();
        $segment->update(['activo' => false]);
        $audit->record($request->user()->id, 'segment.deactivated', 'segmentos_clientes', (int) $segment->id, $before, $segment->fresh()->getAttributes(), $request->ip());

        return back()->with('status', 'Segmento desactivado correctamente.');
    }
}
