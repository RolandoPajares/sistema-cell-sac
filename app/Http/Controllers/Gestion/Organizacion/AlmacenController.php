<?php

namespace App\Http\Controllers\Gestion\Organizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarEstadoAlmacenRequest;
use App\Http\Requests\FiltroAlmacenesRequest;
use App\Http\Requests\GuardarAlmacenRequest;
use App\Models\Organizacion\Almacen;
use App\Models\Organizacion\Sede;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AlmacenController extends Controller
{
    public function index(FiltroAlmacenesRequest $request, ServicioAutorizacion $autorizacion): View
    {
        $usuario = $request->user();
        $permisoConsulta = 'gestion.organizacion.consultar';
        $sedes = $this->consultaSedesAutorizadas($usuario, $autorizacion, $permisoConsulta)
            ->withCount('almacenes')
            ->orderBy('nombre')
            ->orderBy('id')
            ->get(['id', 'codigo', 'nombre', 'activa']);
        $idsSede = $sedes->modelKeys();
        $consultaBase = Almacen::query()->whereIn('sede_id', $idsSede);
        $filtros = $request->validated();

        $consulta = (clone $consultaBase)
            ->with('sede:id,nombre')
            ->select('almacenes.*')
            ->selectSub(
                DB::table('existencias_almacen')
                    ->selectRaw('COALESCE(SUM(cantidad_fisica), 0)')
                    ->whereColumn('almacen_id', 'almacenes.id'),
                'unidades_fisicas',
            )
            ->selectSub(
                DB::table('existencias_almacen')
                    ->selectRaw('COUNT(DISTINCT producto_id)')
                    ->whereColumn('almacen_id', 'almacenes.id')
                    ->where('cantidad_fisica', '>', 0),
                'productos_con_existencias',
            );

        if (filled($filtros['buscar'] ?? null)) {
            $buscar = trim((string) $filtros['buscar']);
            $consulta->where(function (Builder $subconsulta) use ($buscar): void {
                $subconsulta->where('almacenes.codigo', 'like', '%'.$buscar.'%')
                    ->orWhere('almacenes.nombre', 'like', '%'.$buscar.'%')
                    ->orWhereHas('sede', fn (Builder $consultaSede): Builder => $consultaSede->where('nombre', 'like', '%'.$buscar.'%'));
            });
        }

        if (filled($filtros['sede_id'] ?? null)) {
            $consulta->where('sede_id', (int) $filtros['sede_id']);
        }

        if (filled($filtros['tipo'] ?? null)) {
            $consulta->where('tipo', $filtros['tipo']);
        }

        if (filled($filtros['estado'] ?? null)) {
            $consulta->where('activo', $filtros['estado'] === 'activo');
        }

        $almacenes = $consulta->orderBy('nombre')->orderBy('id')->paginate(15)->withQueryString();
        $tipos = (clone $consultaBase)->whereNotNull('tipo')->where('tipo', '<>', '')
            ->distinct()->orderBy('tipo')->pluck('tipo');
        $resumen = (clone $consultaBase)->selectRaw(
            'COUNT(*) AS total, SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) AS activos, SUM(CASE WHEN permite_venta = 1 THEN 1 ELSE 0 END) AS habilitados_venta',
        )->first();
        $unidadesFisicas = DB::table('existencias_almacen as existencia')
            ->join('almacenes as almacen', 'almacen.id', '=', 'existencia.almacen_id')
            ->whereIn('almacen.sede_id', $idsSede)
            ->sum('existencia.cantidad_fisica');
        $productosConExistencias = DB::table('existencias_almacen as existencia')
            ->join('almacenes as almacen', 'almacen.id', '=', 'existencia.almacen_id')
            ->whereIn('almacen.sede_id', $idsSede)
            ->where('existencia.cantidad_fisica', '>', 0)
            ->distinct()->count('existencia.producto_id');
        $existenciasPorSede = DB::table('existencias_almacen as existencia')
            ->join('almacenes as almacen', 'almacen.id', '=', 'existencia.almacen_id')
            ->whereIn('almacen.sede_id', $idsSede)
            ->select('almacen.sede_id')
            ->selectRaw('SUM(existencia.cantidad_fisica) AS unidades_fisicas')
            ->groupBy('almacen.sede_id')
            ->pluck('unidades_fisicas', 'sede_id');
        $resumenSedes = $sedes->map(static fn (Sede $sede): array => [
            'nombre' => $sede->nombre,
            'almacenes' => (int) $sede->almacenes_count,
            'unidades_fisicas' => (float) ($existenciasPorSede->get($sede->id) ?? 0),
        ]);
        $barrasAlmacenes = (clone $consultaBase)
            ->select('almacenes.id', 'almacenes.nombre', 'almacenes.sede_id')
            ->selectSub(
                DB::table('existencias_almacen')
                    ->selectRaw('COALESCE(SUM(cantidad_fisica), 0)')
                    ->whereColumn('almacen_id', 'almacenes.id'),
                'unidades_fisicas',
            )
            ->orderByDesc('unidades_fisicas')->orderBy('nombre')->limit(5)->get();
        $unidadesMaximas = max((float) $barrasAlmacenes->max('unidades_fisicas'), 1);
        $barrasAlmacenes = $barrasAlmacenes->map(static fn (Almacen $almacen): array => [
            'nombre' => $almacen->nombre,
            'unidades_fisicas' => (float) $almacen->unidades_fisicas,
            'porcentaje_relativo' => (int) round(((float) $almacen->unidades_fisicas / $unidadesMaximas) * 100),
        ]);
        $sedesActivas = $this->consultaSedesAutorizadas($usuario, $autorizacion, 'gestion.organizacion.actualizar')
            ->where('activa', true)
            ->orderBy('nombre')->orderBy('id')->get(['id', 'codigo', 'nombre']);

        return view('paneles.administrador.organizacion.almacenes.listar', [
            'almacenes' => $almacenes,
            'filtros' => $filtros,
            'sedes' => $sedes,
            'sedesActivas' => $sedesActivas,
            'tipos' => $tipos,
            'resumen' => [
                'total' => (int) ($resumen->total ?? 0),
                'activos' => (int) ($resumen->activos ?? 0),
                'habilitados_venta' => (int) ($resumen->habilitados_venta ?? 0),
                'unidades_fisicas' => (float) $unidadesFisicas,
                'productos_con_existencias' => (int) $productosConExistencias,
                'sedes_con_almacenes' => $sedes->where('almacenes_count', '>', 0)->count(),
            ],
            'resumenSedes' => $resumenSedes,
            'barrasAlmacenes' => $barrasAlmacenes,
            'puedeEditar' => $autorizacion->allows($usuario, 'gestion.organizacion.actualizar'),
        ]);
    }

    public function store(
        GuardarAlmacenRequest $request,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $datos = $request->validated();
        $usuario = $request->user();

        DB::transaction(function () use ($datos, $usuario, $autorizacion, $auditoria, $request): void {
            $sede = $this->consultaSedesAutorizadas($usuario, $autorizacion, 'gestion.organizacion.actualizar')
                ->whereKey((int) $datos['sede_id'])->lockForUpdate()->first();

            abort_if($sede === null, 404, 'La sede seleccionada no pertenece a tu alcance autorizado.');
            if (! $sede->activa) {
                throw ValidationException::withMessages(['sede_id' => 'Selecciona una sede activa para registrar el almacén.']);
            }

            $almacen = $sede->almacenes()->create([
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'tipo' => $datos['tipo'],
                'permite_venta' => $datos['permite_venta'],
                'activo' => true,
            ]);
            $auditoria->record(
                (int) $usuario->getKey(), 'almacen.registrado', 'almacenes', (int) $almacen->getKey(), null,
                ['codigo' => $almacen->codigo, 'nombre' => $almacen->nombre, 'sede_id' => (int) $sede->getKey()],
                $request->ip(), observations: 'Almacén registrado desde el módulo de organización.',
                scope: ['empresa_id' => (int) $sede->empresa_id, 'sede_id' => (int) $sede->getKey()],
            );
        }, 3);

        return back()->with('status', 'El almacén quedó registrado.');
    }

    public function update(
        GuardarAlmacenRequest $request,
        Almacen $almacen,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $datos = $request->validated();
        $usuario = $request->user();

        DB::transaction(function () use ($datos, $usuario, $autorizacion, $auditoria, $request, $almacen): void {
            $almacen = $this->consultaAlmacenesAutorizados($usuario, $autorizacion, 'gestion.organizacion.actualizar')
                ->whereKey($almacen->getKey())->with('sede')->lockForUpdate()->firstOrFail();
            $valoresAnteriores = [
                'codigo' => $almacen->codigo,
                'nombre' => $almacen->nombre,
                'tipo' => $almacen->tipo,
                'permite_venta' => (bool) $almacen->permite_venta,
            ];
            $almacen->update([
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'tipo' => $datos['tipo'],
                'permite_venta' => $datos['permite_venta'],
            ]);
            $auditoria->record(
                (int) $usuario->getKey(), 'almacen.actualizado', 'almacenes', (int) $almacen->getKey(),
                $valoresAnteriores, [
                    'codigo' => $almacen->codigo,
                    'nombre' => $almacen->nombre,
                    'tipo' => $almacen->tipo,
                    'permite_venta' => (bool) $almacen->permite_venta,
                ], $request->ip(), observations: 'Datos del almacén actualizados.',
                scope: ['empresa_id' => (int) $almacen->sede->empresa_id, 'sede_id' => (int) $almacen->sede_id],
            );
        }, 3);

        return back()->with('status', 'Los datos del almacén quedaron actualizados.');
    }

    public function cambiarEstado(
        CambiarEstadoAlmacenRequest $request,
        Almacen $almacen,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $datos = $request->validated();
        $usuario = $request->user();

        DB::transaction(function () use ($datos, $usuario, $autorizacion, $auditoria, $request, $almacen): void {
            $almacen = $this->consultaAlmacenesAutorizados($usuario, $autorizacion, 'gestion.organizacion.actualizar')
                ->whereKey($almacen->getKey())->with('sede')->lockForUpdate()->firstOrFail();
            $estadoAnterior = (bool) $almacen->activo;
            $estadoNuevo = (bool) $datos['activo'];

            if ($estadoAnterior === $estadoNuevo) {
                return;
            }

            $almacen->update(['activo' => $estadoNuevo]);
            $auditoria->record(
                (int) $usuario->getKey(), $estadoNuevo ? 'almacen.activado' : 'almacen.desactivado',
                'almacenes', (int) $almacen->getKey(), ['activo' => $estadoAnterior],
                ['activo' => $estadoNuevo, 'motivo' => $datos['motivo']], $request->ip(),
                observations: $datos['motivo'],
                scope: ['empresa_id' => (int) $almacen->sede->empresa_id, 'sede_id' => (int) $almacen->sede_id],
            );
        }, 3);

        return back()->with('status', $datos['activo'] ? 'El almacén quedó activado.' : 'El almacén quedó desactivado.');
    }

    /** @return Builder<Sede> */
    private function consultaSedesAutorizadas(User $usuario, ServicioAutorizacion $autorizacion, string $codigoPermiso): Builder
    {
        return Sede::query()->whereIn('id', $autorizacion->authorizedSiteIds($usuario, $codigoPermiso));
    }

    /** @return Builder<Almacen> */
    private function consultaAlmacenesAutorizados(User $usuario, ServicioAutorizacion $autorizacion, string $codigoPermiso): Builder
    {
        return Almacen::query()->whereIn(
            'sede_id',
            $this->consultaSedesAutorizadas($usuario, $autorizacion, $codigoPermiso)->select('id'),
        );
    }
}
