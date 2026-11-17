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
        // Comprueba que la conexion sea MySQL y que existan las tablas necesarias para la auditoria.
        if (
            DB::getDriverName() !== 'mysql'
            || ! Schema::hasTable('registros_auditoria')
            || ! Schema::hasTable('empresas')
            || ! Schema::hasTable('sedes')
        ) {
            return $next($request);
        }

        // Obtiene el usuario autenticado de la solicitud; puede ser null para visitantes.
        $user = $request->user();

        // Obtiene la sede activa del usuario para asociar la operacion con su contexto.
        $siteId = $user instanceof User ? $this->autorizacion->activeSiteIdFor($user) : null;

        // Busca la empresa de la sede activa o usa la empresa principal para solicitudes sin sede.
        $companyId = $siteId !== null
            ? DB::table('sedes')->where('id', $siteId)->value('empresa_id')
            : DB::table('empresas')->where('codigo', 'TECHCELL')->value('id');

        // Define variables de sesion MariaDB que los triggers de auditoria utilizan durante la solicitud.
        DB::statement('SET @tc_user_id = ?, @tc_role_code = ?, @tc_company_id = ?, @tc_site_id = ?', [
            // Guarda el identificador del usuario o null si la solicitud es de un visitante.
            $user instanceof User ? $user->getKey() : null,
            // Guarda el codigo de rol actual del usuario.
            $user instanceof User ? $user->rol : null,
            // Guarda el identificador de la empresa asociada a la operacion.
            $companyId,
            // Guarda el identificador de la sede activa.
            $siteId,
        ]);

        // Ejecuta el siguiente middleware o controlador con el contexto de auditoria preparado.
        try {
            return $next($request);
        } finally {
            // Limpia las variables de sesion para que no afecten otra solicitud o transaccion.
            DB::statement('SET @tc_user_id = NULL, @tc_role_code = NULL, @tc_company_id = NULL, @tc_site_id = NULL');
        }
    }
}
