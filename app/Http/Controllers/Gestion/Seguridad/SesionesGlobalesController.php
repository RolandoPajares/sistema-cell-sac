<?php

namespace App\Http\Controllers\Gestion\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

final class SesionesGlobalesController extends Controller
{
    public function index(Request $request, ServicioAutorizacion $autorizacion): View
    {
        abort_unless($request->user() instanceof User, 403);
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403);
        abort_unless(Schema::hasTable('sesiones_usuarios'), 503, 'El registro de sesiones no está disponible.');

        $sesiones = DB::table('sesiones_usuarios as sesion')->join('usuarios', 'usuarios.id', '=', 'sesion.usuario_id')
            ->select('sesion.id', 'usuarios.nombre as usuario', 'sesion.direccion_ip', 'sesion.agente_usuario', 'sesion.ultimo_acceso', 'sesion.revocada_en')
            ->orderByDesc('sesion.ultimo_acceso')->orderByDesc('sesion.id')->paginate(25)->withQueryString();

        return view('paneles.administrador.seguridad.sesiones.listar', [
            'sesiones' => $sesiones,
            'puedeRevocar' => $autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.revocar'),
        ]);
    }

    public function revoke(Request $request, int $sessionId, RegistradorAuditoria $auditoria, ServicioAutorizacion $autorizacion): RedirectResponse
    {
        abort_unless($request->user() instanceof User, 403);
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.revocar'), 403);
        abort_unless(Schema::hasTable('sesiones_usuarios'), 503, 'El registro de sesiones no está disponible.');
        DB::transaction(function () use ($request, $sessionId, $auditoria): void {
            $sesion = DB::table('sesiones_usuarios')->where('id', $sessionId)->lockForUpdate()->first();
            abort_if($sesion === null, 404);
            if ($sesion->revocada_en === null) {
                DB::table('sesiones_usuarios')->where('id', $sessionId)->update(['revocada_en' => now(), 'actualizado_en' => now()]);
                $auditoria->record($request->user()->getKey(), 'revocar_sesion_global', 'sesiones_usuarios', $sessionId,
                    ['usuario_id' => (int) $sesion->usuario_id, 'revocada' => false], ['usuario_id' => (int) $sesion->usuario_id, 'revocada' => true], $request->ip());
            }
        });

        return back()->with('status', 'La sesión fue revocada. El acceso se cerrará en su siguiente solicitud.');
    }
}
