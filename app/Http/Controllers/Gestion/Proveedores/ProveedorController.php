<?php

namespace App\Http\Controllers\Gestion\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarProveedorRequest;
use App\Http\Requests\GuardarProveedorRequest;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Categoria;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase ProveedorController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ProveedorController extends Controller
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
        $alcanceProductos = $alcance;
        $empresaId = $alcance['empresa_id'];
        $consultaProveedores = $this->consultaProveedores($filtros, $alcance);
        $consultaProductosProveedor = fn (Builder|BelongsToMany $consulta): Builder|BelongsToMany => $consulta->where('productos.empresa_id', $empresaId);
        $consultaComprasAlcance = fn (Builder $consulta): Builder => $this->filtrarComprasPorAlcance($consulta, $alcance);

        $proveedores = $consultaProveedores
            ->with([
                'products' => fn (BelongsToMany $consulta): mixed => $consultaProductosProveedor($consulta)
                    ->select(['productos.id', 'productos.nombre', 'productos.categoria_id'])
                    ->with(['category' => fn (Relation $consultaCategoria): mixed => $consultaCategoria->where('categorias.empresa_id', $empresaId)->select(['categorias.id', 'categorias.nombre'])])
                    ->orderBy('productos.nombre'),
            ])
            ->withCount([
                'products' => $consultaProductosProveedor,
                'purchases' => $consultaComprasAlcance,
            ])
            ->withSum(['purchases as purchases_total' => $consultaComprasAlcance], 'total')
            ->withMax(['purchases as latest_purchase_date' => $consultaComprasAlcance], 'fecha_compra')
            ->orderBy('nombre')
            ->paginate($filtros['por_pagina'])
            ->withQueryString();

        $proveedorSeleccionadoId = $request->integer('proveedor') ?: $proveedores->first()?->id;
        $proveedorSeleccionado = $proveedorSeleccionadoId
            ? $this->detalleProveedor((int) $proveedorSeleccionadoId, $alcance)
            : null;
        $estadosCompra = $proveedorSeleccionado
            ? $this->comprasPorAlcance($alcance)
                ->where('compras.proveedor_id', $proveedorSeleccionado->id)
                ->select('compras.estado')
                ->selectRaw('COUNT(*) AS total')
                ->groupBy('compras.estado')
                ->orderByDesc('total')
                ->get()
            : collect();
        $productosSinExistencias = $inventario->productosParaConsulta($alcanceProductos, ['stock' => 'sin_stock'], true)
            ->whereHas('suppliers', fn (Builder $consulta): Builder => $consulta->where('proveedores.empresa_id', $empresaId))
            ->with(['suppliers' => fn (Relation $consulta): mixed => $consulta->where('proveedores.empresa_id', $empresaId)->orderBy('proveedores.nombre')])
            ->orderBy('existencias_alcance')
            ->orderBy('productos.nombre')
            ->limit(4)
            ->get();

        return view('gestion.comercial.proveedores.listar', [
            'suppliers' => $proveedores,
            'selectedSupplier' => $proveedorSeleccionado,
            'purchaseStatusCounts' => $estadosCompra,
            'filters' => $filtros,
            'categories' => Categoria::query()->where('empresa_id', $empresaId)->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'cities' => Proveedor::query()->where('empresa_id', $empresaId)->whereNotNull('ciudad')->where('ciudad', '<>', '')->distinct()->orderBy('ciudad')->pluck('ciudad'),
            'totalSuppliers' => Proveedor::query()->where('empresa_id', $empresaId)->count(),
            'activeSuppliers' => Proveedor::query()->where('empresa_id', $empresaId)->where('activo', true)->count(),
            'linkedOrders' => $this->comprasPorAlcance($alcance)->where('compras.activo', true)->count(),
            'purchasedTotal' => (float) $this->comprasPorAlcance($alcance)->where('compras.activo', true)->sum('compras.total'),
            'lowStockProducts' => $productosSinExistencias,
            'recentActivity' => $this->actividadReciente($alcance),
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
        $proveedores = $this->consultaProveedores($this->filtros($request), $alcance)
            ->withCount(['products' => fn (Builder $consulta): Builder => $consulta->where('productos.empresa_id', $alcance['empresa_id'])])
            ->orderBy('proveedores.nombre')->limit(5000)->get();
        $rows = $proveedores->map(static fn (Proveedor $proveedor): array => [
            $proveedor->id,
            $proveedor->nombre,
            $proveedor->ruc,
            $proveedor->correo,
            $proveedor->telefono,
            $proveedor->ciudad,
            $proveedor->products_count,
            $proveedor->activo ? 'Activo' : 'Inactivo',
            $proveedor->creado_en?->format('Y-m-d H:i:s'),
        ])->all();
        $filters = $this->filtros($request);
        $response = $exporter->download($format, 'proveedores',
            ['ID', 'Nombre', 'RUC', 'Correo', 'Teléfono', 'Ciudad', 'Productos asociados', 'Estado', 'Registrado'],
            $rows,
            [
                'title' => 'REPORTE DE PROVEEDORES',
                'subtitle' => 'Proveedores reales consultados desde MariaDB.',
                'module' => 'Inventario y Logística',
                'report_name' => 'Proveedores',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Proveedores incluidos', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'proveedores', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

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
    public function store(GuardarProveedorRequest $request, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $proveedor = Proveedor::create($request->validated() + [
            'activo' => true,
            'creado_en' => now(),
            'empresa_id' => $alcance['empresa_id'],
        ]);
        $auditoria->record($request->user()->id, 'supplier.created', 'proveedores', $proveedor->id, null, $proveedor->only(['nombre', 'ruc', 'correo', 'ciudad']), $request->ip());

        return back()->with('status', 'Proveedor registrado.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $supplier  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarProveedorRequest $request, Proveedor $supplier, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $this->verificarEmpresa($supplier, $alcance['empresa_id']);

        $antes = $supplier->only(['nombre', 'ruc', 'correo', 'telefono', 'ciudad', 'activo']);
        $supplier->update($request->validated());
        $auditoria->record($request->user()->id, 'supplier.updated', 'proveedores', $supplier->id, $antes, $supplier->only(array_keys($antes)), $request->ip());

        return back()->with('status', 'Proveedor actualizado.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $supplier  Parámetro recibido por la operación.
     * @param  mixed  $inventario  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(Request $request, Proveedor $supplier, ServicioInventario $inventario, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $alcance = $this->alcanceActual($request, $inventario);
        $this->verificarEmpresa($supplier, $alcance['empresa_id']);

        if ($supplier->activo) {
            $supplier->update(['activo' => false]);
            $auditoria->record($request->user()->id, 'supplier.deactivated', 'proveedores', $supplier->id, ['activo' => true], ['activo' => false], $request->ip());
        }

        return back()->with('status', 'Proveedor desactivado.');
    }

    /**
     * @param  array{buscar:?string,categoria:?int,estado:?string,ciudad:?string,por_pagina:int}  $filtros
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Builder<Proveedor>
     */
    private function consultaProveedores(array $filtros, array $alcance): Builder
    {
        return Proveedor::query()
            ->where('proveedores.empresa_id', $alcance['empresa_id'])
            ->when($filtros['buscar'], function (Builder $consulta, string $busqueda): void {
                $consulta->where(function (Builder $consultaBusqueda) use ($busqueda): void {
                    $consultaBusqueda
                        ->where('proveedores.nombre', 'like', '%'.$busqueda.'%')
                        ->orWhere('proveedores.ruc', 'like', '%'.$busqueda.'%')
                        ->orWhere('proveedores.correo', 'like', '%'.$busqueda.'%')
                        ->orWhere('proveedores.telefono', 'like', '%'.$busqueda.'%')
                        ->orWhere('proveedores.ciudad', 'like', '%'.$busqueda.'%');
                });
            })
            ->when($filtros['categoria'], fn (Builder $consulta, int $categoriaId): Builder => $consulta->whereHas('products', fn (Builder $productos): Builder => $productos
                ->where('productos.empresa_id', $alcance['empresa_id'])
                ->where('productos.categoria_id', $categoriaId)))
            ->when($filtros['estado'] === 'activo', fn (Builder $consulta): Builder => $consulta->where('proveedores.activo', true))
            ->when($filtros['estado'] === 'inactivo', fn (Builder $consulta): Builder => $consulta->where('proveedores.activo', false))
            ->when($filtros['ciudad'], fn (Builder $consulta, string $ciudad): Builder => $consulta->where('proveedores.ciudad', $ciudad));
    }

    /**
     * @return array{buscar:?string,categoria:?int,estado:?string,ciudad:?string,por_pagina:int}
     */
    private function filtros(Request $request): array
    {
        $busqueda = trim($request->string('buscar')->toString());
        $estado = trim($request->string('estado')->toString());
        $ciudad = trim($request->string('ciudad')->toString());
        $porPagina = $request->integer('por_pagina', 10);

        return [
            'buscar' => $busqueda !== '' ? $busqueda : null,
            'categoria' => $request->integer('categoria') ?: null,
            'estado' => in_array($estado, ['activo', 'inactivo'], true) ? $estado : null,
            'ciudad' => $ciudad !== '' ? $ciudad : null,
            'por_pagina' => in_array($porPagina, [10, 20, 50], true) ? $porPagina : 10,
        ];
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     */
    private function detalleProveedor(int $proveedorId, array $alcance): ?Proveedor
    {
        return Proveedor::query()
            ->where('proveedores.empresa_id', $alcance['empresa_id'])
            ->with([
                'products' => fn (BelongsToMany $consulta): mixed => $consulta
                    ->where('productos.empresa_id', $alcance['empresa_id'])
                    ->select(['productos.id', 'productos.nombre', 'productos.categoria_id'])
                    ->with(['category' => fn (Relation $consultaCategoria): mixed => $consultaCategoria->where('categorias.empresa_id', $alcance['empresa_id'])->select(['categorias.id', 'categorias.nombre'])])
                    ->orderBy('productos.nombre'),
                'purchases' => fn (Relation $consulta): mixed => $this->filtrarComprasPorAlcance($consulta->getQuery(), $alcance)
                    ->latest('compras.fecha_compra')
                    ->latest('compras.id')
                    ->limit(5),
            ])
            ->withCount([
                'products' => fn (Builder $consulta): Builder => $consulta->where('productos.empresa_id', $alcance['empresa_id']),
                'purchases' => fn (Builder $consulta): Builder => $this->filtrarComprasPorAlcance($consulta, $alcance),
            ])
            ->withSum(['purchases as purchases_total' => fn (Builder $consulta): Builder => $this->filtrarComprasPorAlcance($consulta, $alcance)], 'total')
            ->withMax(['purchases as latest_purchase_date' => fn (Builder $consulta): Builder => $this->filtrarComprasPorAlcance($consulta, $alcance)], 'fecha_compra')
            ->find($proveedorId);
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Builder<Compra>
     */
    private function comprasPorAlcance(array $alcance): Builder
    {
        return $this->filtrarComprasPorAlcance(Compra::query(), $alcance);
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Builder<Compra>
     */
    private function filtrarComprasPorAlcance(Builder $consulta, array $alcance): Builder
    {
        return $consulta
            ->where('compras.empresa_id', $alcance['empresa_id'])
            ->when($alcance['sede_id'] !== null, fn (Builder $consulta): Builder => $consulta->where('compras.sede_id', $alcance['sede_id']));
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Collection<int, array{icon:string,color:string,title:string,detail:string,date:mixed}>
     */
    private function actividadReciente(array $alcance): Collection
    {
        $idsProveedores = Proveedor::query()->where('empresa_id', $alcance['empresa_id'])->pluck('id');
        $auditorias = $idsProveedores->isEmpty()
            ? collect()
            : RegistroAuditoria::query()
                ->where('entidad', 'proveedores')
                ->whereIn('entidad_id', $idsProveedores)
                ->latest('creado_en')
                ->limit(5)
                ->get(['id', 'accion', 'entidad_id', 'creado_en']);
        $compras = $this->comprasPorAlcance($alcance)
            ->with('supplier:id,nombre')
            ->latest('compras.creado_en')
            ->limit(5)
            ->get(['compras.id', 'compras.proveedor_id', 'compras.total', 'compras.fecha_compra', 'compras.creado_en']);
        $actividades = collect();

        foreach ($auditorias as $auditoria) {
            $actividades->push([
                'icon' => match ($auditoria->accion) {
                    'supplier.created' => 'person-plus',
                    'supplier.deactivated' => 'x-circle',
                    default => 'pencil-square',
                },
                'color' => match ($auditoria->accion) {
                    'supplier.deactivated' => 'red',
                    'supplier.created' => 'green',
                    default => 'blue',
                },
                'title' => match ($auditoria->accion) {
                    'supplier.created' => 'Nuevo proveedor registrado',
                    'supplier.deactivated' => 'Proveedor desactivado',
                    default => 'Proveedor actualizado',
                },
                'detail' => 'Registro de proveedor #'.$auditoria->entidad_id,
                'date' => $auditoria->creado_en,
            ]);
        }

        foreach ($compras as $compra) {
            $actividades->push([
                'icon' => 'cart-plus',
                'color' => 'purple',
                'title' => 'Nueva orden de compra',
                'detail' => ($compra->supplier?->nombre ?: 'Proveedor no disponible').' · S/ '.number_format((float) $compra->total, 2),
                'date' => $compra->creado_en,
            ]);
        }

        return $actividades->sortByDesc('date')->take(5)->values();
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
     * @param  mixed  $proveedor  Parámetro recibido por la operación.
     * @param  mixed  $empresaId  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function verificarEmpresa(Proveedor $proveedor, int $empresaId): void
    {
        abort_unless((int) $proveedor->empresa_id === $empresaId, 404);
    }
}
