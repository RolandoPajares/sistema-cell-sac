<?php

namespace App\Http\Controllers\Gestion\Analitica\DashboardGlobal;

use App\Http\Controllers\Controller;
use App\Models\Comunicacion\Notificacion;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Notificaciones\ServicioNotificaciones;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase AlertasGlobalesController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class AlertasGlobalesController extends Controller
{
    /**
     * Ejecuta la operación mostrar definida por la clase AlertasGlobalesController.
     *
     * @param  mixed  $solicitud  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function mostrar(Request $solicitud, ServicioNotificaciones $servicio): View
    {
        // Guarda en $filtros el resultado necesario para continuar esta operacion.
        $filtros = $solicitud->validate([
            'estado' => ['nullable', 'string', Rule::in(['todas', 'no_leidas', 'leidas'])],
            'buscar' => ['nullable', 'string', 'max:120'],
            'notificacion' => ['nullable', 'uuid'],
        ]);
        // Guarda en $usuarioActual el resultado necesario para continuar esta operacion.
        $usuarioActual = $solicitud->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuarioActual instanceof User, 401);

        // Guarda en $disponible el resultado necesario para continuar esta operacion.
        $disponible = Schema::hasTable('notificaciones');
        // Guarda en $estado el resultado necesario para continuar esta operacion.
        $estado = (string) ($filtros['estado'] ?? 'todas');
        // Guarda en $busqueda el resultado necesario para continuar esta operacion.
        $busqueda = isset($filtros['buscar']) ? trim($filtros['buscar']) : null;
        // Guarda en $notificaciones el resultado necesario para continuar esta operacion.
        $notificaciones = $disponible
            ? $servicio->listarDelUsuario($usuarioActual, $estado, $busqueda)
            : collect();
        // Guarda en $resumen el resultado necesario para continuar esta operacion.
        $resumen = $disponible ? $servicio->obtenerResumenDelUsuario($usuarioActual) : null;
        // Guarda en $notificacionSeleccionada el resultado necesario para continuar esta operacion.
        $notificacionSeleccionada = null;

        // Comprueba la condicion $disponible && isset($filtros['notificacion']) antes de ejecutar este bloque.
        if ($disponible && isset($filtros['notificacion'])) {
            // Guarda en $notificacionSeleccionada el resultado necesario para continuar esta operacion.
            $notificacionSeleccionada = $servicio->buscarDelUsuario($usuarioActual, $filtros['notificacion']);
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($notificacionSeleccionada === null, 404);
        } elseif ($disponible) {
            // Guarda en $notificacionSeleccionada el resultado necesario para continuar esta operacion.
            $notificacionSeleccionada = $notificaciones->first();
        }

        // Guarda en $exportParameters el resultado necesario para continuar esta operacion.
        $exportParameters = array_filter([
            'estado' => $estado,
            'buscar' => $busqueda,
        ], static fn ($value): bool => $value !== null && $value !== '');

        // Agrupa en $header los datos que se utilizaran en la siguiente operacion.
        $header = [
            'titulo' => 'Alertas y notificaciones',
            'icono' => 'bi-bell',
            'descripcion' => 'Consulta las notificaciones persistidas para la cuenta administrativa actual.',
            'exportRoute' => $disponible ? 'management.reports.dashboard_alerts.export' : null,
            'exportParameters' => $exportParameters,
        ];
        $metrics = $resumen === null ? [] : [
            ['titulo' => 'Notificaciones', 'icono' => 'bi-bell', 'valor' => number_format($resumen['total']), 'detalle' => 'Registros asociados a esta cuenta'],
            ['titulo' => 'No leídas', 'icono' => 'bi-envelope-exclamation', 'tono' => 'rojo', 'valor' => number_format($resumen['no_leidas']), 'detalle' => 'Según leido_en en MariaDB'],
            ['titulo' => 'Leídas', 'icono' => 'bi-envelope-open', 'tono' => 'verde', 'valor' => number_format($resumen['leidas']), 'detalle' => 'Según leido_en en MariaDB'],
            ['titulo' => 'Tipos registrados', 'icono' => 'bi-tags', 'tono' => 'violeta', 'valor' => number_format($resumen['tipos']), 'detalle' => 'Tipos distintos almacenados'],
        ];
        $chart = $resumen === null ? [] : [
            'centro' => number_format($resumen['total']),
            'etiqueta' => 'Notificaciones',
            'gradiente' => $resumen['gradiente'],
            'leyenda' => $resumen['distribucion'],
        ];

        // Devuelve la vista 'gestion.analitica.dashboard-global.alertas.listar' con los datos preparados por este controlador.
        return view('gestion.analitica.dashboard-global.alertas.listar', [
            'disponible' => $disponible,
            'estado' => $estado,
            'busqueda' => $busqueda,
            'notificaciones' => $notificaciones,
            'resumen' => $resumen,
            'notificacionSeleccionada' => $notificacionSeleccionada,
            'header' => $header,
            'metrics' => $metrics,
            'chart' => $chart,
        ]);
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $solicitud  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $exportador  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function exportar(Request $solicitud, string $format, ExportadorTabular $exportador, RegistradorAuditoria $auditoria): Response|StreamedResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('notificaciones'), 503, 'La tabla de notificaciones no está disponible.');

        // Guarda en $filtros el resultado necesario para continuar esta operacion.
        $filtros = $solicitud->validate([
            'estado' => ['nullable', 'string', Rule::in(['todas', 'no_leidas', 'leidas'])],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);
        // Guarda en $usuario el resultado necesario para continuar esta operacion.
        $usuario = $solicitud->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 401);
        // Prepara en $consulta la consulta que obtiene o modifica los registros necesarios.
        $consulta = Notificacion::query()
            ->where('tipo_notificable', $usuario->getMorphClass())
            ->where('notificable_id', $usuario->getKey())
            ->when(($filtros['estado'] ?? 'todas') === 'no_leidas', fn (Builder $q) => $q->whereNull('leido_en'))
            ->when(($filtros['estado'] ?? 'todas') === 'leidas', fn (Builder $q) => $q->whereNotNull('leido_en'))
            ->when(($filtros['buscar'] ?? '') !== '', function (Builder $q) use ($filtros): void {
                // Guarda en $termino el resultado necesario para continuar esta operacion.
                $termino = '%'.trim((string) $filtros['buscar']).'%';
                $q->where(fn (Builder $sub) => $sub->where('tipo', 'like', $termino)->orWhere('datos', 'like', $termino));
            })
            ->orderByDesc('creado_en')->orderByDesc('id');
        // Guarda en $filas el resultado necesario para continuar esta operacion.
        $filas = $consulta->get(['id', 'tipo', 'datos', 'leido_en', 'creado_en'])
            ->map(static fn (Notificacion $notificacion): array => [
                $notificacion->id,
                $notificacion->tipo,
                $notificacion->datos,
                $notificacion->leido_en,
                $notificacion->creado_en,
            ])->all();
        // Guarda en $respuesta el resultado necesario para continuar esta operacion.
        $respuesta = $exportador->download($format, 'notificaciones', ['ID', 'Tipo', 'Datos', 'Leída en', 'Creada en'], $filas, [
            'title' => 'NOTIFICACIONES ADMINISTRATIVAS',
            'subtitle' => 'Datos reales filtrados desde MariaDB.',
            'module' => 'Dashboard Global',
            'report_name' => 'Notificaciones',
            'filters' => $filtros,
            'generated_by' => (string) $usuario->nombre,
            'role' => 'Usuario autorizado',
            'scope' => 'Notificaciones del destinatario autenticado.',
            'indicators' => [['label' => 'Registros incluidos', 'value' => count($filas)]],
            'column_types' => ['integer', 'text', 'text', 'datetime', 'datetime'],
        ]);
        $auditoria->record((int) $usuario->getKey(), 'exportar', 'notificaciones', null, null, [
            'formato' => $format, 'filtros' => $filtros, 'cantidad' => count($filas),
        ], $solicitud->ip());

        return $respuesta;
    }
}
