<?php

namespace App\Http\Controllers\Gestion\Organizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Organizacion\GuardarResponsableOrganizacionalRequest;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioResponsablesOrganizacionales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

final class ResponsableOrganizacionalController extends Controller
{
    public function index(ServicioAutorizacion $autorizacion, ServicioResponsablesOrganizacionales $servicio): View
    {
        $usuario = request()->user();
        abort_unless($usuario instanceof User, 403);
        $idsSede = $autorizacion->authorizedSiteIds($usuario, 'gestion.organizacion.consultar');
        $estructuraDisponible = Schema::hasTable('responsables_organizacionales');
        $responsables = $estructuraDisponible ? $servicio->listar($idsSede) : collect();
        $sedes = DB::table('sedes')->whereIn('id', $idsSede)->where('activa', true)->orderBy('nombre')->get(['id', 'empresa_id', 'nombre', 'codigo']);
        $almacenes = DB::table('almacenes')->whereIn('sede_id', $idsSede)->where('activo', true)->orderBy('nombre')->get(['id', 'sede_id', 'nombre', 'codigo']);
        $usuarios = DB::table('usuarios_sedes_roles as asignacion')->join('usuarios', 'usuarios.id', '=', 'asignacion.usuario_id')
            ->whereIn('asignacion.sede_id', $idsSede)->where('asignacion.activo', true)->where('usuarios.activo', true)
            ->where(fn ($consulta) => $consulta->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', now()))
            ->select('usuarios.id', 'usuarios.nombre', 'asignacion.sede_id')->distinct()->orderBy('usuarios.nombre')->get();

        return view('paneles.administrador.organizacion.responsables.listar', [
            'responsables' => $responsables,
            'sedes' => $sedes,
            'almacenes' => $almacenes,
            'usuarios' => $usuarios,
            'estructuraDisponible' => $estructuraDisponible,
            'puedeEditar' => $autorizacion->allowsGlobal($usuario, 'gestion.organizacion.actualizar'),
            'total' => $responsables->count(),
            'vigentes' => $responsables->filter(fn ($registro): bool => $registro->activo && $registro->vigente_desde->lte(today()) && ($registro->vigente_hasta === null || $registro->vigente_hasta->gte(today())))->count(),
        ]);
    }

    public function store(GuardarResponsableOrganizacionalRequest $request, ServicioResponsablesOrganizacionales $servicio, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        $servicio->reasignar($usuario, $request->validated(), $auditoria, $request->ip());

        return back()->with('status', 'La asignación del responsable fue registrada y auditada.');
    }
}
