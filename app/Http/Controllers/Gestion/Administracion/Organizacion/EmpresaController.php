<?php

namespace App\Http\Controllers\Gestion\Administracion\Organizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Administracion\Organizacion\ActualizarEmpresaRequest;
use App\Models\Organizacion\Empresa;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();
        $empresa = $empresaId === null ? null : Empresa::query()
            ->withCount('sedes')
            ->withCount(['sedes as sedes_activas_count' => static function (Builder $consulta): void {
                $consulta->where('activa', true);
            }])
            ->find($empresaId);
        $almacenes = $empresaId === null ? 0 : DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('sedes.empresa_id', $empresaId)->count();
        // Guarda en $sedesActivas el resultado necesario para continuar esta operacion.
        $sedesActivas = (int) ($empresa?->sedes_activas_count ?? 0);

        // Devuelve la vista 'gestion.administracion.organizacion.datos-empresa' con los datos preparados por este controlador.
        return view('gestion.administracion.organizacion.datos-empresa', [
            'empresa' => $empresa,
            'almacenes' => $almacenes,
            'sedesActivas' => $sedesActivas > 0 ? '1' : '0',
            'metricas' => $empresa === null ? [] : [
                ['titulo' => 'Estado de la empresa', 'valor' => $empresa->activo ? 'Activa' : 'Inactiva', 'icono' => 'bi-buildings', 'detalle' => 'Estado almacenado en MariaDB'],
                ['titulo' => 'Sede interna', 'valor' => $sedesActivas > 0 ? 'Activa' : 'Revisar', 'icono' => 'bi-geo-alt', 'tono' => 'verde', 'detalle' => 'Contexto operativo autorizado'],
                ['titulo' => 'Almacenes asociados', 'valor' => $almacenes, 'icono' => 'bi-box-seam', 'tono' => 'violeta', 'detalle' => 'Relacionados mediante las sedes'],
                ['titulo' => 'Moneda base', 'valor' => $empresa->moneda_base, 'icono' => 'bi-currency-exchange', 'detalle' => 'Código registrado para la empresa'],
            ],
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($empresaId === null, 409, 'Debe existir una empresa activa autorizada antes de actualizar sus datos.');

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($datos, $usuario, $empresaId, $auditoria, $request): void {
            // Prepara en $empresa la consulta que obtiene o modifica los registros necesarios.
            $empresa = Empresa::query()->whereKey($empresaId)->where('activo', true)->lockForUpdate()->firstOrFail();
            // Agrupa en $valoresAnteriores los datos que se utilizaran en la siguiente operacion.
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

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Los datos de la empresa quedaron actualizados.');
    }
}
