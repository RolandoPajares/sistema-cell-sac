<?php

namespace App\Http\Controllers\Gestion\SoporteReclamos;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCasoSoporteRequest;
use App\Http\Requests\GuardarCasoSoporteRequest;
use App\Models\Comercio\CasoSoporte;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SoporteReclamoController extends Controller
{
    public function index(Request $request, ServicioAutorizacion $authorization): View
    {
        $module = require resource_path('views/gestion/soporte-reclamos/datos.php');
        $siteIds = $authorization->authorizedSiteIds($request->user(), 'gestion.soporte.consultar');
        $query = CasoSoporte::query()->with('cliente:id,nombre_contacto,razon_social');
        if ($siteIds === []) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('sede_id', $siteIds);
        }
        $search = trim($request->string('buscar')->toString());
        $estado = trim($request->string('estado')->toString());
        $prioridad = trim($request->string('prioridad')->toString());
        $query->when($search !== '', static function (Builder $builder) use ($search): void {
            $like = '%'.$search.'%';
            $builder->where(static function (Builder $nested) use ($like): void {
                $nested->where('codigo', 'like', $like)->orWhere('asunto', 'like', $like);
            });
        });
        $query->when(in_array($estado, ['abierto', 'en_proceso', 'resuelto', 'cerrado'], true), fn (Builder $builder) => $builder->where('estado', $estado));
        $query->when(in_array($prioridad, ['baja', 'normal', 'alta', 'urgente'], true), fn (Builder $builder) => $builder->where('prioridad', $prioridad));
        $all = clone $query;
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
            'record' => ['id' => $case->id, 'codigo' => $case->codigo, 'asunto' => $case->asunto, 'prioridad' => $case->prioridad, 'estado' => $case->estado, 'cliente_id' => $case->cliente_id],
            'edit_url' => route('management.support.update', $case),
            'delete_url' => route('management.support.close', $case),
        ])->all();
        $module['pagination'] = $cases;
        $module['filters'] = ['buscar' => $search, 'estado' => $estado, 'prioridad' => $prioridad];
        $module['kpis'][0]['value'] = (string) (clone $all)->where('estado', 'abierto')->count();
        $module['kpis'][1]['value'] = (string) (clone $all)->where('estado', 'en_proceso')->count();
        $module['kpis'][2]['value'] = (string) (clone $all)->whereIn('estado', ['resuelto', 'cerrado'])->count();
        $module['kpis'][3]['value'] = (string) (clone $all)->whereIn('prioridad', ['alta', 'urgente'])->count();

        return view('gestion.soporte-reclamos.listar', compact('module'));
    }

    public function store(GuardarCasoSoporteRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $location = app(ServicioAutorizacion::class)->operationSite($request->user());
        abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null, 403, 'Selecciona una sede autorizada.');
        $case = DB::transaction(function () use ($request, $location, $audit): CasoSoporte {
            $case = CasoSoporte::create($request->validated() + ['empresa_id' => $location['empresa_id'], 'sede_id' => $location['sede_id'], 'estado' => 'abierto']);
            $audit->record($request->user()->getKey(), 'support.created', 'casos_soporte', $case->getKey(), null, $case->only(['codigo', 'asunto', 'prioridad', 'estado']), $request->ip());

            return $case;
        }, 3);

        return redirect()->route('management.support')->with('status', 'Caso de soporte registrado: '.$case->codigo.'.');
    }

    public function update(ActualizarCasoSoporteRequest $request, CasoSoporte $casoSoporte, RegistradorAuditoria $audit): RedirectResponse
    {
        $this->assertCaseInScope($request, $casoSoporte, 'gestion.soporte.actualizar');
        $before = $casoSoporte->only(['asunto', 'prioridad', 'estado', 'cliente_id']);
        DB::transaction(function () use ($request, $casoSoporte, $audit, $before): void {
            $casoSoporte->update($request->validated());
            $audit->record($request->user()->getKey(), 'support.updated', 'casos_soporte', $casoSoporte->getKey(), $before, $casoSoporte->only(array_keys($before)), $request->ip());
        }, 3);

        return redirect()->route('management.support')->with('status', 'Caso de soporte actualizado.');
    }

    public function deactivate(Request $request, CasoSoporte $casoSoporte, RegistradorAuditoria $audit): RedirectResponse
    {
        $this->assertCaseInScope($request, $casoSoporte, 'gestion.soporte.anular');
        abort_unless($casoSoporte->estado !== 'cerrado', 409, 'El caso ya está cerrado.');
        $before = ['estado' => $casoSoporte->estado];
        $casoSoporte->update(['estado' => 'cerrado']);
        $audit->record($request->user()->getKey(), 'support.closed', 'casos_soporte', $casoSoporte->getKey(), $before, ['estado' => 'cerrado'], $request->ip());

        return redirect()->route('management.support')->with('status', 'Caso de soporte cerrado.');
    }

    private function assertCaseInScope(Request $request, CasoSoporte $case, string $permission): void
    {
        abort_unless(app(ServicioAutorizacion::class)->allowsRecord($request->user(), $permission, (int) $case->empresa_id, (int) $case->sede_id), 403);
    }

    public function export(Request $request, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'): Response|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $siteIds = $this->authorizationSites($request, 'gestion.soporte.exportar');
        $query = CasoSoporte::query()->with('cliente:id,nombre_contacto,razon_social');
        $query->whereIn('sede_id', $siteIds);
        $search = trim($request->string('buscar')->toString());
        $query->when($search !== '', static function (Builder $builder) use ($search): void {
            $like = '%'.$search.'%';
            $builder->where(static fn (Builder $nested): Builder => $nested->where('codigo', 'like', $like)->orWhere('asunto', 'like', $like));
        });
        $query->when($request->filled('estado'), fn (Builder $builder) => $builder->where('estado', $request->string('estado')->toString()));
        $query->when($request->filled('prioridad'), fn (Builder $builder) => $builder->where('prioridad', $request->string('prioridad')->toString()));
        $records = $query->orderByDesc('actualizado_en')->orderByDesc('id')->get();
        $rows = $records->map(static fn (CasoSoporte $case): array => [$case->codigo, $case->cliente?->nombre_contacto ?: $case->cliente?->razon_social ?: 'Sin cliente', $case->asunto, $case->prioridad, $case->estado, $case->actualizado_en?->toDateTimeString()])->all();
        $response = $exporter->download($format, 'casos-soporte', ['Ticket', 'Cliente', 'Asunto', 'Prioridad', 'Estado', 'Actualización'], $rows, [
            'title' => 'REPORTE DE SOPORTE Y RECLAMOS', 'module' => 'Operaciones Comerciales', 'report_name' => 'Casos de soporte', 'generated_by' => (string) $request->user()->nombre,
            'indicators' => [['label' => 'Casos incluidos', 'value' => count($rows)]], 'column_types' => ['text', 'text', 'text', 'text', 'text', 'datetime'],
        ]);
        $audit->record($request->user()->getKey(), 'exportar', 'casos_soporte', null, null, ['formato' => $format, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /** @return array<int, int> */
    private function authorizationSites(Request $request, string $permission): array
    {
        return app(ServicioAutorizacion::class)->authorizedSiteIds($request->user(), $permission);
    }
}
