<?php

namespace App\Http\Controllers\Gestion\Soporte\Reclamos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Soporte\Reclamos\ActualizarCasoSoporteRequest;
use App\Http\Requests\Gestion\Soporte\Reclamos\GuardarCasoSoporteRequest;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\CasoSoporte;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase SoporteReclamoController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class SoporteReclamoController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioAutorizacion $authorization): View
    {
        // Guarda en $module el resultado necesario para continuar esta operacion.
        $module = require config_path('gestion/soporte/reclamos.php');
        // Obtiene en $siteIds la informacion de la solicitud o del usuario actual.
        $siteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.soporte.consultar');
        // Prepara en $query la consulta que obtiene o modifica los registros necesarios.
        $query = CasoSoporte::query()->with('cliente:id,nombre_contacto,razon_social');
        // Comprueba la condicion $siteIds === [] antes de ejecutar este bloque.
        if ($siteIds === []) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('sede_id', $siteIds);
        }
        // Guarda en $search el resultado necesario para continuar esta operacion.
        $search = trim($request->string('buscar')->toString());
        // Guarda en $estado el resultado necesario para continuar esta operacion.
        $estado = trim($request->string('estado')->toString());
        // Guarda en $prioridad el resultado necesario para continuar esta operacion.
        $prioridad = trim($request->string('prioridad')->toString());
        $query->when($search !== '', static function (Builder $builder) use ($search): void {
            // Guarda en $like el resultado necesario para continuar esta operacion.
            $like = '%'.$search.'%';
            $builder->where(static function (Builder $nested) use ($like): void {
                $nested->where('codigo', 'like', $like)->orWhere('asunto', 'like', $like);
            });
        });
        $query->when(in_array($estado, ['abierto', 'en_proceso', 'resuelto', 'cerrado'], true), fn (Builder $builder) => $builder->where('estado', $estado));
        $query->when(in_array($prioridad, ['baja', 'normal', 'alta', 'urgente'], true), fn (Builder $builder) => $builder->where('prioridad', $prioridad));
        // Guarda en $all el resultado necesario para continuar esta operacion.
        $all = clone $query;
        // Guarda en $cases el resultado necesario para continuar esta operacion.
        $cases = $query->orderByDesc('actualizado_en')->orderByDesc('id')->paginate(15)->withQueryString();
        $module['backend'] = true;
        $module['rows'] = $cases->getCollection()->map(static fn (CasoSoporte $case): array => [
            'id' => $case->id,
            'codigo' => $case->codigo,
            'cliente' => $case->cliente?->nombre_contacto ?: $case->cliente?->razon_social ?: 'Sin cliente',
            'asunto' => $case->asunto,
            'prioridad' => $case->prioridad,
            'estado' => $case->estado,
            'actualizado' => $case->actualizado_en?->format('d/m/Y H:i'),
            'record' => ['id' => $case->id, 'codigo' => $case->codigo, 'asunto' => $case->asunto, 'prioridad' => $case->prioridad, 'estado' => $case->estado, 'cliente_id' => $case->cliente_id, 'cliente_nombre' => $case->cliente?->nombre_contacto ?: $case->cliente?->razon_social ?: 'Sin cliente'],
            'edit_url' => route('management.support.update', $case),
            'delete_url' => route('management.support.close', $case),
        ])->all();
        $module['pagination'] = $cases;
        $module['filters'] = ['buscar' => $search, 'estado' => $estado, 'prioridad' => $prioridad];
        $module['tableColumns'] = ['Ticket', 'Cliente', 'Asunto', 'Prioridad', 'Estado', 'Actualización', 'Acciones'];
        $module['kpis'][0]['value'] = (string) (clone $all)->where('estado', 'abierto')->count();
        $module['kpis'][1]['value'] = (string) (clone $all)->where('estado', 'en_proceso')->count();
        $module['kpis'][2]['value'] = (string) (clone $all)->whereIn('estado', ['resuelto', 'cerrado'])->count();
        $module['kpis'][3]['value'] = (string) (clone $all)->whereIn('prioridad', ['alta', 'urgente'])->count();

        // Devuelve la vista 'gestion.soporte.reclamos.listar' con los datos preparados por este controlador.
        return view('gestion.soporte.reclamos.listar', compact('module'));
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarCasoSoporteRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        // Obtiene en $location la informacion de la solicitud o del usuario actual.
        $location = app(ServicioAutorizacion::class)->operationSite($request->user());
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null, 403, 'Selecciona una sede autorizada.');
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->comprobarClienteEnAlcance($request, $request->filled('cliente_id') ? $request->integer('cliente_id') : null, (int) $location['empresa_id']);
        // Prepara en $case la consulta que obtiene o modifica los registros necesarios.
        $case = DB::transaction(function () use ($request, $location, $audit): CasoSoporte {
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $case = CasoSoporte::create($request->validated() + ['empresa_id' => $location['empresa_id'], 'sede_id' => $location['sede_id'], 'estado' => 'abierto']);
            $audit->record($request->user()->getKey(), 'support.created', 'casos_soporte', $case->getKey(), null, $case->only(['codigo', 'asunto', 'prioridad', 'estado']), $request->ip());

            return $case;
        }, 3);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.support')->with('status', 'Caso de soporte registrado: '.$case->codigo.'.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $casoSoporte  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarCasoSoporteRequest $request, CasoSoporte $casoSoporte, RegistradorAuditoria $audit): RedirectResponse
    {
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->comprobarCasoEnAlcance($request, $casoSoporte, 'gestion.soporte.actualizar');
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->comprobarClienteEnAlcance($request, $request->filled('cliente_id') ? $request->integer('cliente_id') : null, (int) $casoSoporte->empresa_id);
        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $casoSoporte->only(['asunto', 'prioridad', 'estado', 'cliente_id']);
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $casoSoporte, $audit, $before): void {
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $casoSoporte->update($request->validated());
            $audit->record($request->user()->getKey(), 'support.updated', 'casos_soporte', $casoSoporte->getKey(), $before, $casoSoporte->only(array_keys($before)), $request->ip());
        }, 3);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.support')->with('status', 'Caso de soporte actualizado.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $casoSoporte  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(Request $request, CasoSoporte $casoSoporte, RegistradorAuditoria $audit): RedirectResponse
    {
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->comprobarCasoEnAlcance($request, $casoSoporte, 'gestion.soporte.anular');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($casoSoporte->estado !== 'cerrado', 409, 'El caso ya está cerrado.');
        $before = ['estado' => $casoSoporte->estado];
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $casoSoporte, $audit, $before): void {
            $casoSoporte->update(['estado' => 'cerrado']);
            $audit->record($request->user()->getKey(), 'support.closed', 'casos_soporte', $casoSoporte->getKey(), $before, ['estado' => 'cerrado'], $request->ip());
        }, 3);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('management.support')->with('status', 'Caso de soporte cerrado.');
    }

    /**
     * Ejecuta la operación assertCaseInScope definida por la clase SoporteReclamoController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $case  Parámetro recibido por la operación.
     * @param  mixed  $permission  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function comprobarCasoEnAlcance(Request $request, CasoSoporte $case, string $permission): void
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(app(ServicioAutorizacion::class)->allowsRecord($request->user(), $permission, (int) $case->empresa_id, (int) $case->sede_id), 403);
    }

    /**
     * Ejecuta la operación assertCustomerInScope definida por la clase SoporteReclamoController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $customerId  Parámetro recibido por la operación.
     * @param  mixed  $companyId  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function comprobarClienteEnAlcance(Request $request, ?int $customerId, int $companyId): void
    {
        // Comprueba la condicion $customerId === null antes de ejecutar este bloque.
        if ($customerId === null) {
            return;
        }

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Cliente::query()
            ->whereKey($customerId)
            ->where('empresa_id', $companyId)
            ->where('activo', true)
            ->exists(), 422, 'El cliente no pertenece a la empresa o está inactivo.');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'): Response|StreamedResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Guarda en $siteIds el resultado necesario para continuar esta operacion.
        $siteIds = $this->sedesAutorizadas($request, 'gestion.soporte.exportar');
        // Prepara en $query la consulta que obtiene o modifica los registros necesarios.
        $query = CasoSoporte::query()->with('cliente:id,nombre_contacto,razon_social');
        $query->whereIn('sede_id', $siteIds);
        // Guarda en $search el resultado necesario para continuar esta operacion.
        $search = trim($request->string('buscar')->toString());
        $query->when($search !== '', static function (Builder $builder) use ($search): void {
            // Guarda en $like el resultado necesario para continuar esta operacion.
            $like = '%'.$search.'%';
            $builder->where(static fn (Builder $nested): Builder => $nested->where('codigo', 'like', $like)->orWhere('asunto', 'like', $like));
        });
        $query->when($request->filled('estado'), fn (Builder $builder) => $builder->where('estado', $request->string('estado')->toString()));
        $query->when($request->filled('prioridad'), fn (Builder $builder) => $builder->where('prioridad', $request->string('prioridad')->toString()));
        // Guarda en $records el resultado necesario para continuar esta operacion.
        $records = $query->orderByDesc('actualizado_en')->orderByDesc('id')->get();
        $rows = $records->map(static fn (CasoSoporte $case): array => [$case->codigo, $case->cliente?->nombre_contacto ?: $case->cliente?->razon_social ?: 'Sin cliente', $case->asunto, $case->prioridad, $case->estado, $case->actualizado_en?->toDateTimeString()])->all();
        // Guarda en $response el resultado necesario para continuar esta operacion.
        $response = $exporter->download($format, 'casos-soporte', ['Ticket', 'Cliente', 'Asunto', 'Prioridad', 'Estado', 'Actualización'], $rows, [
            'title' => 'REPORTE DE SOPORTE Y RECLAMOS', 'module' => 'Operaciones Comerciales', 'report_name' => 'Casos de soporte', 'generated_by' => (string) $request->user()->nombre,
            'indicators' => [['label' => 'Casos incluidos', 'value' => count($rows)]], 'column_types' => ['text', 'text', 'text', 'text', 'text', 'datetime'],
        ]);
        $audit->record($request->user()->getKey(), 'exportar', 'casos_soporte', null, null, ['formato' => $format, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /** @return array<int, int> */
    private function sedesAutorizadas(Request $request, string $permission): array
    {
        return app(ServicioAutorizacion::class)->authorizedSiteIds($request->user(), $permission);
    }
}
