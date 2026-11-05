<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioConfiguracionSistema;
use App\Servicios\ServicioNotificaciones;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ConfiguracionSistemaController extends Controller
{
    public function general(Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio): View
    {
        $datos = $servicio->obtenerDatosGenerales($request->user(), $autorizacion->operationSite($request->user()));

        return $this->pagina('general', $datos);
    }

    public function integraciones(Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', Rule::in(['Activa', 'Inactiva'])],
        ]);
        $datos = $servicio->consultarIntegraciones(
            $autorizacion->operationSite($request->user()),
            $filtros['buscar'] ?? null,
            $filtros['estado'] ?? null,
        );

        return $this->pagina('integraciones', $datos);
    }

    public function notificaciones(Request $request, ServicioNotificaciones $servicio): View
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::in(['todas', 'no_leidas', 'leidas'])],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);
        $usuario = $request->user();
        $paginador = $servicio->listarDelUsuario($usuario, $filtros['estado'] ?? 'todas', $filtros['buscar'] ?? null);
        $resumen = $servicio->obtenerResumenDelUsuario($usuario);

        return $this->pagina('notificaciones', [
            'notificaciones' => $paginador,
            'resumen' => $resumen,
            'canales' => collect(),
            'reglas' => collect(),
            'plantillas' => collect(),
        ]);
    }

    public function seguridad(Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio): View
    {
        $datos = $servicio->obtenerDatosSeguridad(
            $request->user(),
            $autorizacion->operationSite($request->user()),
        );

        return $this->pagina('seguridad', $datos);
    }

    public function respaldos(Request $request, ServicioAutorizacion $autorizacion): View
    {
        $autorizacion->operationSite($request->user());

        return $this->pagina('respaldos', ['respaldos' => collect(), 'historialDisponible' => false]);
    }

    private function pagina(string $tipo, array $datos): View
    {
        return view('paneles.administrador.configuracion-sistema.'.$tipo.'.listar', $datos + ['tipo' => $tipo]);
    }
}
