<?php

namespace App\Http\Controllers\Gestion\Organizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarEmpresaRequest;
use App\Models\Organizacion\Empresa;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase EmpresaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class EmpresaController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioAutorizacion $autorizacion): View
    {
        $empresaId = $autorizacion->activeCompanyId();
        $empresa = $empresaId === null ? null : Empresa::query()
            ->withCount('sedes')
            ->withCount(['sedes as sedes_activas_count' => static function (Builder $consulta): void {
                $consulta->where('activa', true);
            }])
            ->withCount(['sedes as sedes_inactivas_count' => static function (Builder $consulta): void {
                $consulta->where('activa', false);
            }])
            ->find($empresaId);
        $almacenes = $empresaId === null ? 0 : DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('sedes.empresa_id', $empresaId)->count();

        return view('gestion.organizacion.empresa.listar', [
            'empresa' => $empresa,
            'almacenes' => $almacenes,
            'puedeEditar' => $request->user() instanceof User
                && $autorizacion->allows($request->user(), 'gestion.organizacion.actualizar'),
        ]);
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @return mixed Resultado de la operación.
     */
    public function update(
        ActualizarEmpresaRequest $request,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $datos = $request->validated();
        $usuario = $request->user();
        $empresaId = $autorizacion->activeCompanyId();

        abort_if($empresaId === null, 409, 'Debe existir una empresa activa autorizada antes de actualizar sus datos.');

        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request): void {
            $empresa = Empresa::query()->whereKey($empresaId)->where('activo', true)->lockForUpdate()->firstOrFail();
            $valoresAnteriores = [
                'razon_social' => $empresa->razon_social,
                'nombre_comercial' => $empresa->nombre_comercial,
                'ruc' => $empresa->ruc,
                'moneda_base' => $empresa->moneda_base,
            ];
            $empresa->update([
                'razon_social' => $datos['razon_social'],
                'nombre_comercial' => $datos['nombre_comercial'] ?? null,
                'ruc' => $datos['ruc'] ?? null,
                'moneda_base' => $datos['moneda_base'],
            ]);
            $auditoria->record(
                (int) $usuario->getKey(), 'empresa.actualizada', 'empresas', (int) $empresa->getKey(), $valoresAnteriores,
                [
                    'razon_social' => $empresa->razon_social,
                    'nombre_comercial' => $empresa->nombre_comercial,
                    'ruc' => $empresa->ruc,
                    'moneda_base' => $empresa->moneda_base,
                ], $request->ip(), observations: 'Datos de empresa actualizados desde el panel de organización.',
                scope: ['empresa_id' => (int) $empresa->getKey()],
            );
        }, 3);

        return back()->with('status', 'Los datos de la empresa quedaron actualizados.');
    }
}
