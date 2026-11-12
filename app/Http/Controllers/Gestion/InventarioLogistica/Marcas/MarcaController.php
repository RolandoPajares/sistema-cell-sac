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
        // Guarda en $alcance el resultado necesario para continuar esta operacion.
        $alcance = $this->alcance($request, $inventario);
        // Guarda en $buscar el resultado necesario para continuar esta operacion.
        $buscar = trim((string) $request->string('buscar'));
        // Guarda en $estado el resultado necesario para continuar esta operacion.
        $estado = $request->string('estado')->toString();
        // Prepara en $marcas la consulta que obtiene o modifica los registros necesarios.
        $marcas = Marca::query()->where('empresa_id', $alcance['empresa_id'])
            ->withCount(['productos' => fn ($query) => $query->where('productos.empresa_id', $alcance['empresa_id'])])
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', '%'.$buscar.'%'))
            ->when($estado === 'activa', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactiva', fn ($query) => $query->where('activo', false))
            ->orderBy('nombre')->paginate(10)->withQueryString();

        // Devuelve la vista 'gestion.inventario-logistica.marcas.listar' con los datos preparados por este controlador.
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
        // Guarda en $alcance el resultado necesario para continuar esta operacion.
        $alcance = $this->alcance($request, $inventario);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $marca = Marca::create($request->validated() + ['empresa_id' => $alcance['empresa_id'], 'activo' => true]);
        $auditoria->record($request->user()->id, 'brand.created', 'marcas', $marca->id, null, $marca->only(['nombre', 'descripcion', 'activo']), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Marca registrada correctamente.');
    }

    public function update(ActualizarMarcaRequest $request, Marca $marca, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Guarda en $alcance el resultado necesario para continuar esta operacion.
        $alcance = $this->alcance($request, $inventario);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless((int) $marca->empresa_id === $alcance['empresa_id'], 404);
        // Guarda en $antes el resultado necesario para continuar esta operacion.
        $antes = $marca->only(['nombre', 'descripcion', 'activo']);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $marca->update($request->validated());
        $auditoria->record($request->user()->id, 'brand.updated', 'marcas', $marca->id, $antes, $marca->only(array_keys($antes)), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Marca actualizada correctamente.');
    }

    public function deactivate(Request $request, Marca $marca, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Guarda en $alcance el resultado necesario para continuar esta operacion.
        $alcance = $this->alcance($request, $inventario);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless((int) $marca->empresa_id === $alcance['empresa_id'], 404);
        $marca->update(['activo' => false]);
        $auditoria->record($request->user()->id, 'brand.deactivated', 'marcas', $marca->id, ['activo' => true], ['activo' => false], $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Marca desactivada correctamente.');
    }

    private function alcance(Request $request, ServicioInventario $inventario): array
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($request->user() instanceof User, 403);

        return $inventario->alcanceConsulta($request->user(), app(ServicioAutorizacion::class)->permissionForRoute($request) ?? 'gestion.productos.consultar');
    }
}
