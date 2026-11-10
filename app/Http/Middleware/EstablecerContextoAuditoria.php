<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establece y limpia las variables de sesión MariaDB usadas por la auditoría de la solicitud.
 */
final class EstablecerContextoAuditoria
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * Establece en la solicitud el contexto de auditoría usado por las operaciones posteriores.
     *
     * @param  Request  $request  Solicitud cuyo usuario, ruta y entidad se usarán como contexto.
     * @param  Closure  $next  Siguiente etapa del pipeline HTTP.
     * @return Response Respuesta producida por el siguiente middleware o controlador.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            DB::getDriverName() !== 'mysql'
            || ! Schema::hasTable('registros_auditoria')
            || ! Schema::hasTable('empresas')
            || ! Schema::hasTable('sedes')
        ) {
            return $next($request);
        }

        $user = $request->user();
        $siteId = $user instanceof User ? $this->autorizacion->activeSiteIdFor($user) : null;
        $companyId = $siteId !== null
            ? DB::table('sedes')->where('id', $siteId)->value('empresa_id')
            : DB::table('empresas')->where('codigo', 'TECHCELL')->value('id');

        DB::statement('SET @tc_user_id = ?, @tc_role_code = ?, @tc_company_id = ?, @tc_site_id = ?', [
            $user instanceof User ? $user->getKey() : null,
            $user instanceof User ? $user->rol : null,
            $companyId,
            $siteId,
        ]);

        try {
            return $next($request);
        } finally {
            DB::statement('SET @tc_user_id = NULL, @tc_role_code = NULL, @tc_company_id = NULL, @tc_site_id = NULL');
        }
    }
}
