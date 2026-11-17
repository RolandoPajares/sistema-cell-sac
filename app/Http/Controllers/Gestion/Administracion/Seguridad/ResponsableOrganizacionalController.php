<?php

namespace App\Http\Controllers\Gestion\Administracion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Administracion\Seguridad\GuardarResponsableOrganizacionalRequest;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Administracion\Seguridad\ServicioResponsablesOrganizacionales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase ResponsableOrganizacionalController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ResponsableOrganizacionalController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(ServicioAutorizacion $autorizacion, ServicioResponsablesOrganizacionales $servicio): View
    {
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = request()->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Guarda en $idsSede el resultado necesario para continuar esta operacion.
        $idsSede = $autorizacion->authorizedSiteIds($usuario, 'gestion.organizacion.consultar');
        // Guarda en $estructuraDisponible el resultado necesario para continuar esta operacion.
        $estructuraDisponible = Schema::hasTable('responsables_organizacionales');
        // Guarda en $responsables el resultado necesario para continuar esta operacion.
        $responsables = $estructuraDisponible ? $servicio->listar($idsSede) : collect();
        // Prepara en $sedes la consulta que obtiene o modifica los registros necesarios.
        $sedes = DB::table('sedes')->whereIn('id', $idsSede)->where('activa', true)->orderBy('nombre')->get(['id', 'empresa_id', 'nombre', 'codigo']);
        // Prepara en $almacenes la consulta que obtiene o modifica los registros necesarios.
        $almacenes = DB::table('almacenes')->whereIn('sede_id', $idsSede)->where('activo', true)->orderBy('nombre')->get(['id', 'sede_id', 'nombre', 'codigo']);
        // Prepara en $usuarios la consulta que obtiene o modifica los registros necesarios.
        $usuarios = DB::table('usuarios_sedes_roles as asignacion')->join('usuarios', 'usuarios.id', '=', 'asignacion.usuario_id')
            ->whereIn('asignacion.sede_id', $idsSede)->where('asignacion.activo', true)->where('usuarios.activo', true)
            ->where(fn ($consulta) => $consulta->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', now()))
            ->select('usuarios.id', 'usuarios.nombre', 'asignacion.sede_id')->distinct()->orderBy('usuarios.nombre')->get();

        // Devuelve la vista 'gestion.administracion.seguridad.responsables.listar' con los datos preparados por este controlador.
        return view('gestion.administracion.seguridad.responsables.listar', [
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

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarResponsableOrganizacionalRequest $request, ServicioResponsablesOrganizacionales $servicio, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $servicio->reasignar($usuario, $request->validated(), $auditoria, $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'La asignación del responsable fue registrada y auditada.');
    }
}
