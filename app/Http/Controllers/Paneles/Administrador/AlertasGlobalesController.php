<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Comunicacion\Notificacion;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioNotificaciones;
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
        $filtros = $solicitud->validate([
            'estado' => ['nullable', 'string', Rule::in(['todas', 'no_leidas', 'leidas'])],
            'buscar' => ['nullable', 'string', 'max:120'],
            'notificacion' => ['nullable', 'uuid'],
        ]);
        $usuarioActual = $solicitud->user();
        abort_unless($usuarioActual instanceof User, 401);

        $disponible = Schema::hasTable('notificaciones');
        $estado = (string) ($filtros['estado'] ?? 'todas');
        $busqueda = isset($filtros['buscar']) ? trim($filtros['buscar']) : null;
        $notificaciones = $disponible
            ? $servicio->listarDelUsuario($usuarioActual, $estado, $busqueda)
            : collect();
        $resumen = $disponible ? $servicio->obtenerResumenDelUsuario($usuarioActual) : null;
        $notificacionSeleccionada = null;

        if ($disponible && isset($filtros['notificacion'])) {
            $notificacionSeleccionada = $servicio->buscarDelUsuario($usuarioActual, $filtros['notificacion']);
            abort_if($notificacionSeleccionada === null, 404);
        } elseif ($disponible) {
            $notificacionSeleccionada = $notificaciones->first();
        }

        return view('paneles.administrador.dashboard-global.alertas.listar', [
            'disponible' => $disponible,
            'estado' => $estado,
            'busqueda' => $busqueda,
            'notificaciones' => $notificaciones,
            'resumen' => $resumen,
            'notificacionSeleccionada' => $notificacionSeleccionada,
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
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        abort_unless(Schema::hasTable('notificaciones'), 503, 'La tabla de notificaciones no está disponible.');

        $filtros = $solicitud->validate([
            'estado' => ['nullable', 'string', Rule::in(['todas', 'no_leidas', 'leidas'])],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);
        $usuario = $solicitud->user();
        abort_unless($usuario instanceof User, 401);
        $consulta = Notificacion::query()
            ->where('tipo_notificable', $usuario->getMorphClass())
            ->where('notificable_id', $usuario->getKey())
            ->when(($filtros['estado'] ?? 'todas') === 'no_leidas', fn (Builder $q) => $q->whereNull('leido_en'))
            ->when(($filtros['estado'] ?? 'todas') === 'leidas', fn (Builder $q) => $q->whereNotNull('leido_en'))
            ->when(($filtros['buscar'] ?? '') !== '', function (Builder $q) use ($filtros): void {
                $termino = '%'.trim((string) $filtros['buscar']).'%';
                $q->where(fn (Builder $sub) => $sub->where('tipo', 'like', $termino)->orWhere('datos', 'like', $termino));
            })
            ->orderByDesc('creado_en')->orderByDesc('id');
        $filas = $consulta->get(['id', 'tipo', 'datos', 'leido_en', 'creado_en'])
            ->map(static fn (Notificacion $notificacion): array => [
                $notificacion->id,
                $notificacion->tipo,
                $notificacion->datos,
                $notificacion->leido_en,
                $notificacion->creado_en,
            ])->all();
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
