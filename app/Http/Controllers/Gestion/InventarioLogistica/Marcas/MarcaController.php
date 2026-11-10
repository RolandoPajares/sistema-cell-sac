<?php

namespace App\Http\Controllers\Gestion\InventarioLogistica\Marcas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\InventarioLogistica\Marcas\ActualizarMarcaRequest;
use App\Http\Requests\Gestion\InventarioLogistica\Marcas\GuardarMarcaRequest;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarcaController extends Controller
{
    public function index(Request $request, ServicioInventario $inventario): View
    {
        $alcance = $this->alcance($request, $inventario);
        $buscar = trim((string) $request->string('buscar'));
        $estado = $request->string('estado')->toString();
        $marcas = Marca::query()->where('empresa_id', $alcance['empresa_id'])
            ->withCount(['productos' => fn ($query) => $query->where('productos.empresa_id', $alcance['empresa_id'])])
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', '%'.$buscar.'%'))
            ->when($estado === 'activa', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactiva', fn ($query) => $query->where('activo', false))
            ->orderBy('nombre')->paginate(10)->withQueryString();

        return view('gestion.inventario-logistica.marcas.listar', [
            'marcas' => $marcas,
            'buscar' => $buscar,
            'estado' => $estado,
            'total' => Marca::where('empresa_id', $alcance['empresa_id'])->count(),
            'activas' => Marca::where('empresa_id', $alcance['empresa_id'])->where('activo', true)->count(),
            'inactivas' => Marca::where('empresa_id', $alcance['empresa_id'])->where('activo', false)->count(),
            'productos' => Producto::where('empresa_id', $alcance['empresa_id'])->count(),
        ]);
    }

    public function store(GuardarMarcaRequest $request, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcance($request, $inventario);
        $marca = Marca::create($request->validated() + ['empresa_id' => $alcance['empresa_id'], 'activo' => true]);
        $auditoria->record($request->user()->id, 'brand.created', 'marcas', $marca->id, null, $marca->only(['nombre', 'descripcion', 'activo']), $request->ip());

        return back()->with('status', 'Marca registrada correctamente.');
    }

    public function update(ActualizarMarcaRequest $request, Marca $marca, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcance($request, $inventario);
        abort_unless((int) $marca->empresa_id === $alcance['empresa_id'], 404);
        $antes = $marca->only(['nombre', 'descripcion', 'activo']);
        $marca->update($request->validated());
        $auditoria->record($request->user()->id, 'brand.updated', 'marcas', $marca->id, $antes, $marca->only(array_keys($antes)), $request->ip());

        return back()->with('status', 'Marca actualizada correctamente.');
    }

    public function deactivate(Request $request, Marca $marca, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcance($request, $inventario);
        abort_unless((int) $marca->empresa_id === $alcance['empresa_id'], 404);
        $marca->update(['activo' => false]);
        $auditoria->record($request->user()->id, 'brand.deactivated', 'marcas', $marca->id, ['activo' => true], ['activo' => false], $request->ip());

        return back()->with('status', 'Marca desactivada correctamente.');
    }

    private function alcance(Request $request, ServicioInventario $inventario): array
    {
        abort_unless($request->user() instanceof User, 403);

        return $inventario->alcanceConsulta($request->user(), app(ServicioAutorizacion::class)->permissionForRoute($request) ?? 'gestion.productos.consultar');
    }
}
