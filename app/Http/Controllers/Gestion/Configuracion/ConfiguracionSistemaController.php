<?php

namespace App\Http\Controllers\Gestion\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Configuracion\ActualizarIntegracionRequest;
use App\Http\Requests\Gestion\Configuracion\GuardarConfiguracionSistemaRequest;
use App\Http\Requests\Gestion\Configuracion\GuardarIntegracionRequest;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Notificaciones\ServicioNotificaciones;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Configuracion\ServicioConfiguracionSistema;
use App\Servicios\Gestion\Configuracion\ServicioPresentacionConfiguracion;
use App\Servicios\Gestion\Configuracion\ServicioRespaldos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase ConfiguracionSistemaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ConfiguracionSistemaController extends Controller
{
    public function __construct(private ServicioPresentacionConfiguracion $presentacion) {}

    /**
     * Ejecuta la operación general definida por la clase ConfiguracionSistemaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function general(Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio): View
    {
        // Obtiene en $ubicacion la informacion de la solicitud o del usuario actual.
        $ubicacion = $autorizacion->operationSite($request->user());
        // Obtiene en $datos la informacion de la solicitud o del usuario actual.
        $datos = $servicio->obtenerDatosGenerales($request->user(), $ubicacion);
        $datos['configuracion'] = $ubicacion['empresa_id'] === null
            ? []
            : $servicio->consultarConfiguracion('general', (int) $ubicacion['empresa_id'], $ubicacion['sede_id']);
        $datos['ubicacion'] = $ubicacion;
        $datos['canUpdateConfiguration'] = $autorizacion->allowsGlobal($request->user(), 'gestion.configuracion.actualizar');

        return $this->pagina('general', $datos);
    }

    public function configuracionGrupo(string $grupo, Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio): View
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($grupo, ['parametros-comerciales', 'inventario', 'finanzas'], true), 404);
        // Obtiene en $ubicacion la informacion de la solicitud o del usuario actual.
        $ubicacion = $autorizacion->operationSite($request->user());

        // Devuelve la vista 'gestion.configuracion.' con los datos preparados por este controlador.
        return view('gestion.configuracion.'.$grupo.'.configurar', [
            'grupo' => $grupo,
            'configuracion' => $ubicacion['empresa_id'] === null
                ? []
                : $servicio->consultarConfiguracion($grupo, (int) $ubicacion['empresa_id'], $ubicacion['sede_id']),
            'sedes' => $ubicacion['empresa_id'] === null ? collect() : $servicio->sedesConfigurables((int) $ubicacion['empresa_id']),
            'sedeId' => $ubicacion['sede_id'],
            'canUpdateConfiguration' => $autorizacion->allowsGlobal($request->user(), 'gestion.configuracion.actualizar'),
        ]);
    }

    public function actualizarConfiguracion(
        GuardarConfiguracionSistemaRequest $request,
        ServicioAutorizacion $autorizacion,
        ServicioConfiguracionSistema $servicio,
    ): RedirectResponse {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $ubicacion el resultado necesario para continuar esta operacion.
        $ubicacion = $autorizacion->operationSite($usuario);
        $servicio->guardarConfiguracion(
            $datos['grupo'],
            $datos['alcance'],
            $datos['sede_id'] ?? null,
            $datos['configuracion'],
            $usuario,
            $ubicacion,
            $request->ip(),
        );

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route(match ($datos['grupo']) {
            'general' => 'management.general_settings',
            'parametros-comerciales' => 'management.commercial_parameters',
            'inventario' => 'management.inventory_settings',
            default => 'management.finance_settings',
        })->with('status', 'La configuración se actualizó correctamente.');
    }

    /**
     * Ejecuta la operación integraciones definida por la clase ConfiguracionSistemaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function integraciones(Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio): View
    {
        // Guarda en $filtros el resultado necesario para continuar esta operacion.
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', Rule::in(['Activa', 'Inactiva'])],
        ]);
        // Guarda en $datos el resultado necesario para continuar esta operacion.
        $datos = $servicio->consultarIntegraciones(
            $autorizacion->operationSite($request->user()),
            $filtros['buscar'] ?? null,
            $filtros['estado'] ?? null,
        );
        $datos['filtros'] = $filtros;

        return $this->pagina('integraciones', $datos);
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @return mixed Resultado de la operación.
     */
    public function actualizarIntegracion(
        ActualizarIntegracionRequest $request,
        int $integracion,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('integraciones'), 503, 'La tabla de integraciones no está disponible.');

        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $ubicacion el resultado necesario para continuar esta operacion.
        $ubicacion = $autorizacion->operationSite($usuario);

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $integracion, $usuario, $ubicacion, $auditoria): void {
            // Prepara en $registro la consulta que obtiene o modifica los registros necesarios.
            $registro = DB::table('integraciones')
                ->where('id', $integracion)
                ->where('empresa_id', $ubicacion['empresa_id'])
                ->lockForUpdate()
                ->first();
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($registro !== null, 404, 'La integración no pertenece a la empresa autorizada.');

            // Guarda en $anterior el resultado necesario para continuar esta operacion.
            $anterior = (bool) $registro->activa;
            // Guarda en $actual el resultado necesario para continuar esta operacion.
            $actual = (bool) $request->boolean('activa');
            // Comprueba la condicion $anterior === $actual antes de ejecutar este bloque.
            if ($anterior === $actual) {
                return;
            }

            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('integraciones')->where('id', $integracion)->update([
                'activa' => $actual,
                'actualizado_en' => now(),
            ]);
            $auditoria->record(
                $usuario->getKey(),
                $actual ? 'integracion.activada' : 'integracion.desactivada',
                'integraciones',
                $integracion,
                ['activa' => $anterior],
                ['activa' => $actual],
                $request->ip(),
                scope: ['empresa_id' => (int) $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id'] !== null ? (int) $ubicacion['sede_id'] : null],
            );
        }, 3);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'El estado de la integración se actualizó en MariaDB.');
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function guardarIntegracion(GuardarIntegracionRequest $request, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('integraciones'), 503, 'La tabla de integraciones no está disponible.');
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $ubicacion el resultado necesario para continuar esta operacion.
        $ubicacion = $autorizacion->operationSite($usuario);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($ubicacion['empresa_id'] !== null, 422, 'No existe una empresa autorizada para registrar la integración.');
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();

        // Prepara en $id la consulta que obtiene o modifica los registros necesarios.
        $id = DB::transaction(function () use ($datos, $usuario, $ubicacion, $request, $auditoria): int {
            // Prepara en $duplicada la consulta que obtiene o modifica los registros necesarios.
            $duplicada = DB::table('integraciones')->where('empresa_id', $ubicacion['empresa_id'])->where('codigo', $datos['codigo'])->exists();
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($duplicada, 422, 'Ya existe una integración con ese código en la empresa autorizada.');
            // Prepara en $id la consulta que obtiene o modifica los registros necesarios.
            $id = DB::table('integraciones')->insertGetId([
                'empresa_id' => $ubicacion['empresa_id'],
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'proveedor' => $datos['proveedor'],
                'activa' => true,
                'referencia_secreto' => null,
                'configuracion_publica' => json_encode([], JSON_THROW_ON_ERROR),
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
            $auditoria->record($usuario->getKey(), 'integracion.creada', 'integraciones', $id, null, $datos, $request->ip(),
                scope: ['empresa_id' => (int) $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id'] !== null ? (int) $ubicacion['sede_id'] : null]);

            return (int) $id;
        }, 3);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.integrations')->with('status', 'La integración #'.$id.' quedó registrada en MariaDB.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $integracion  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function editarIntegracion(GuardarIntegracionRequest $request, int $integracion, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('integraciones'), 503, 'La tabla de integraciones no está disponible.');
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $ubicacion el resultado necesario para continuar esta operacion.
        $ubicacion = $autorizacion->operationSite($usuario);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $datos = $request->validated();

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($datos, $integracion, $usuario, $ubicacion, $request, $auditoria): void {
            // Prepara en $registro la consulta que obtiene o modifica los registros necesarios.
            $registro = DB::table('integraciones')->where('id', $integracion)->where('empresa_id', $ubicacion['empresa_id'])->lockForUpdate()->first();
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($registro !== null, 404, 'La integración no pertenece a la empresa autorizada.');
            // Prepara en $duplicada la consulta que obtiene o modifica los registros necesarios.
            $duplicada = DB::table('integraciones')->where('empresa_id', $ubicacion['empresa_id'])->where('codigo', $datos['codigo'])->where('id', '<>', $integracion)->exists();
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($duplicada, 422, 'Ya existe otra integración con ese código en la empresa autorizada.');
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('integraciones')->where('id', $integracion)->update([...$datos, 'actualizado_en' => now()]);
            $auditoria->record($usuario->getKey(), 'integracion.actualizada', 'integraciones', $integracion,
                ['codigo' => $registro->codigo, 'nombre' => $registro->nombre, 'proveedor' => $registro->proveedor], $datos, $request->ip(),
                scope: ['empresa_id' => (int) $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id'] !== null ? (int) $ubicacion['sede_id'] : null]);
        }, 3);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Los datos de la integración se actualizaron en MariaDB.');
    }

    /**
     * Ejecuta la operación notificaciones definida por la clase ConfiguracionSistemaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function notificaciones(Request $request, ServicioNotificaciones $servicio): View
    {
        // Guarda en $filtros el resultado necesario para continuar esta operacion.
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::in(['todas', 'no_leidas', 'leidas'])],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Guarda en $paginador el resultado necesario para continuar esta operacion.
        $paginador = $servicio->listarDelUsuario($usuario, $filtros['estado'] ?? 'todas', $filtros['buscar'] ?? null);
        // Guarda en $resumen el resultado necesario para continuar esta operacion.
        $resumen = $servicio->obtenerResumenDelUsuario($usuario);

        return $this->pagina('notificaciones', [
            'notificaciones' => $paginador,
            'resumen' => $resumen,
            'filtros' => $filtros,
            'canales' => collect(),
            'reglas' => collect(),
            'plantillas' => collect(),
        ]);
    }

    /**
     * Ejecuta la operación seguridad definida por la clase ConfiguracionSistemaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function seguridad(Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio): View
    {
        // Guarda en $datos el resultado necesario para continuar esta operacion.
        $datos = $servicio->obtenerDatosSeguridad(
            $request->user(),
            $autorizacion->operationSite($request->user()),
        );

        return $this->pagina('seguridad', $datos);
    }

    /**
     * Ejecuta la operación respaldos definida por la clase ConfiguracionSistemaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $respaldos  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function respaldos(Request $request, ServicioAutorizacion $autorizacion, ServicioRespaldos $respaldos): View
    {
        $autorizacion->operationSite($request->user());

        return $this->pagina('respaldos', [
            'respaldos' => $respaldos->listar(),
            'historialDisponible' => true,
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $respaldos  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function crearRespaldo(Request $request, ServicioRespaldos $respaldos, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
        try {
            // Obtiene en $respaldo la informacion de la solicitud o del usuario actual.
            $respaldo = $respaldos->crear($request->user(), $request->ip(), $auditoria);
        } catch (\RuntimeException) {
            // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
            return back()->with('error', 'No se puede generar el respaldo desde esta conexión. Solicita autorización al administrador del sistema.');
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.backups')->with('status', 'Respaldo MariaDB generado: '.$respaldo['nombre']);
    }

    /**
     * Ejecuta la operación descargarRespaldo definida por la clase ConfiguracionSistemaController.
     *
     * @param  mixed  $archivo  Parámetro recibido por la operación.
     * @param  mixed  $respaldos  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function descargarRespaldo(string $archivo, ServicioRespaldos $respaldos)
    {
        // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
        return response()->download($respaldos->rutaSegura($archivo), $archivo, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Ejecuta la operación pagina definida por la clase ConfiguracionSistemaController.
     *
     * @param  mixed  $tipo  Parámetro recibido por la operación.
     * @param  mixed  $datos  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    private function pagina(string $tipo, array $datos): View
    {
        // Guarda en $vista el resultado necesario para continuar esta operacion.
        $vista = match ($tipo) {
            'integraciones' => 'integraciones.listar',
            'respaldos' => 'respaldos.listar',
            default => $tipo.'.configurar',
        };

        // Devuelve la vista 'gestion.configuracion.' con los datos preparados por este controlador.
        return view('gestion.configuracion.'.$vista, $this->presentacion->preparar($tipo, $datos));
    }
}
