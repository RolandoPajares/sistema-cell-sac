<?php

namespace App\Http\Controllers\Gestion\Categorias;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCategoriaRequest;
use App\Http\Requests\GuardarCategoriaRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase CategoriaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CategoriaController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioInventario $inventario): View
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $filtros = $this->filtros($request);
        $resumen = $this->resumenCategorias($alcance['empresa_id']);

        return view('gestion.inventario.categorias.listar', [
            'categories' => $this->consultaCategorias($filtros, $alcance['empresa_id'])->paginate(7)->withQueryString(),
            'filters' => $filtros,
            'totalCategories' => $resumen['total'],
            'activeCategories' => $resumen['activas'],
            'inactiveCategories' => $resumen['inactivas'],
            'assignedProducts' => Producto::query()->where('empresa_id', $alcance['empresa_id'])->count(),
        ]);
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, ServicioInventario $inventario, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $format = (string) ($request->route('format') ?: 'csv');
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $filters = $this->filtros($request);
        $categorias = $this->consultaCategorias($filters, $alcance['empresa_id'])->limit(5000)->get();
        $rows = $categorias->map(static fn (Categoria $categoria): array => [
            $categoria->id,
            $categoria->nombre,
            $categoria->descripcion,
            $categoria->products_count,
            $categoria->activo ? 'Activa' : 'Inactiva',
            $categoria->creado_en?->format('Y-m-d H:i:s'),
        ])->all();
        $response = $exporter->download($format, 'categorias',
            ['ID', 'Nombre', 'Descripción', 'Productos', 'Estado', 'Registrada'],
            $rows,
            [
                'title' => 'REPORTE DE CATEGORÍAS',
                'subtitle' => 'Categorías reales consultadas desde MariaDB.',
                'module' => 'Inventario y Logística',
                'report_name' => 'Categorías',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Categorías incluidas', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'categorias', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarCategoriaRequest $request, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $categoria = Categoria::create($request->validated() + [
            'activo' => true,
            'creado_en' => now(),
            'empresa_id' => $alcance['empresa_id'],
        ]);
        $auditoria->record($request->user()->id, 'category.created', 'categorias', $categoria->id, null, $categoria->only(['nombre', 'descripcion']), $request->ip());

        return back()->with('status', 'Categoría registrada.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $category  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarCategoriaRequest $request, Categoria $category, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $this->verificarEmpresa($category, $alcance['empresa_id']);

        $antes = $category->only(['nombre', 'descripcion', 'activo']);
        $category->update($request->validated());
        $auditoria->record($request->user()->id, 'category.updated', 'categorias', $category->id, $antes, $category->only(array_keys($antes)), $request->ip());

        return back()->with('status', 'Categoría actualizada.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $category  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(Request $request, Categoria $category, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $this->verificarEmpresa($category, $alcance['empresa_id']);

        if ($category->activo) {
            $category->update(['activo' => false]);
            $auditoria->record($request->user()->id, 'category.deactivated', 'categorias', $category->id, ['activo' => true], ['activo' => false], $request->ip());
        }

        return back()->with('status', 'Categoría desactivada.');
    }

    /**
     * @param  array{buscar:?string,estado:?string,orden:string}  $filtros
     * @return Builder<Categoria>
     */
    private function consultaCategorias(array $filtros, int $empresaId): Builder
    {
        return Categoria::query()
            ->where('categorias.empresa_id', $empresaId)
            ->withCount(['products' => fn (Builder $consulta): Builder => $consulta->where('productos.empresa_id', $empresaId)])
            ->when($filtros['buscar'], function (Builder $consulta, string $busqueda): void {
                $consulta->where(function (Builder $consultaBusqueda) use ($busqueda): void {
                    $consultaBusqueda
                        ->where('categorias.nombre', 'like', '%'.$busqueda.'%')
                        ->orWhere('categorias.descripcion', 'like', '%'.$busqueda.'%');
                });
            })
            ->when($filtros['estado'] === 'activa', fn (Builder $consulta): Builder => $consulta->where('categorias.activo', true))
            ->when($filtros['estado'] === 'inactiva', fn (Builder $consulta): Builder => $consulta->where('categorias.activo', false))
            ->when($filtros['orden'] === 'nombre_desc', fn (Builder $consulta): Builder => $consulta->orderByDesc('categorias.nombre'))
            ->when($filtros['orden'] === 'reciente', fn (Builder $consulta): Builder => $consulta->orderByDesc('categorias.creado_en'))
            ->when($filtros['orden'] === 'nombre_asc', fn (Builder $consulta): Builder => $consulta->orderBy('categorias.nombre'));
    }

    /**
     * @return array{buscar:?string,estado:?string,orden:string}
     */
    private function filtros(Request $request): array
    {
        $busqueda = trim((string) $request->input('buscar', ''));
        $estado = trim((string) $request->input('estado', ''));
        $orden = trim((string) $request->input('orden', 'nombre_asc'));

        return [
            'buscar' => $busqueda !== '' ? $busqueda : null,
            'estado' => in_array($estado, ['activa', 'inactiva'], true) ? $estado : null,
            'orden' => in_array($orden, ['nombre_asc', 'nombre_desc', 'reciente'], true) ? $orden : 'nombre_asc',
        ];
    }

    /**
     * @return array{total:int,activas:int,inactivas:int}
     */
    private function resumenCategorias(int $empresaId): array
    {
        $resumen = Categoria::query()
            ->where('empresa_id', $empresaId)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) AS activas')
            ->selectRaw('SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) AS inactivas')
            ->first();

        return [
            'total' => (int) ($resumen->total ?? 0),
            'activas' => (int) ($resumen->activas ?? 0),
            'inactivas' => (int) ($resumen->inactivas ?? 0),
        ];
    }

    /**
     * @return array{empresa_id:int,sede_id:?int}
     */
    private function alcanceActual(Request $request, ServicioInventario $inventario): array
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);

        $permiso = app(ServicioAutorizacion::class)->permissionForRoute($request);
        abort_unless($permiso !== null, 403);

        return $inventario->alcanceConsulta($usuario, $permiso);
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $categoria  Parámetro recibido por la operación.
     * @param  mixed  $empresaId  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function verificarEmpresa(Categoria $categoria, int $empresaId): void
    {
        abort_unless((int) $categoria->empresa_id === $empresaId, 404);
    }
}
