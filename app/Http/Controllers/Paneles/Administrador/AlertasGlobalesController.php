<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Servicios\ServicioNotificaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AlertasGlobalesController extends Controller
{
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
}
