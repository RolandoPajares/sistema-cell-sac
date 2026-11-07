<?php

namespace App\Http\Controllers\Gestion\Campanias;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCampaniaRequest;
use App\Http\Requests\GuardarCampaniaRequest;
use App\Http\Requests\ImportarCampaniasRequest;
use App\Models\Marketing\Campania;
use App\Models\Marketing\EventoCampania;
use App\Models\Marketing\SegmentoCliente;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase CampaniaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CampaniaController extends Controller
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
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para consultar campañas.');
        $filters = $this->filters($request);
        $consultaEmpresa = Campania::query()->where('empresa_id', $empresaId);
        $campaigns = $this->campaignQuery($filters, $empresaId)
            ->with('segments')
            ->withCount('events')
            ->orderByDesc('vistas')
            ->orderByDesc('id')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $totalViews = (int) (clone $consultaEmpresa)->sum('vistas');
        $totalClicks = (int) (clone $consultaEmpresa)->sum('clics');
        $currentMonth = [now()->startOfMonth(), now()->endOfMonth()];
        $previousMonth = [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()];
        $currentViews = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'vista')->whereBetween('creado_en', $currentMonth)->count();
        $previousViews = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'vista')->whereBetween('creado_en', $previousMonth)->count();
        $currentClicks = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'clic')->whereBetween('creado_en', $currentMonth)->count();
        $previousClicks = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'clic')->whereBetween('creado_en', $previousMonth)->count();

        return view('gestion.marketing.campanias.listar', [
            'campaigns' => $campaigns,
            'segments' => SegmentoCliente::query()->where('activo', true)->orderBy('nombre')->get(),
            'filters' => $filters,
            'totalCampaigns' => (clone $consultaEmpresa)->count(),
            'activeCampaigns' => (clone $consultaEmpresa)->where('activo', true)->count(),
            'totalViews' => $totalViews,
            'totalClicks' => $totalClicks,
            'conversionRate' => $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 1) : 0,
            'viewsTrend' => $this->trendPercent($currentViews, $previousViews),
            'clicksTrend' => $this->trendPercent($currentClicks, $previousClicks),
            'topCampaigns' => (clone $consultaEmpresa)->with('segments')->orderByDesc('vistas')->orderByDesc('clics')->limit(5)->get(),
            'distribution' => (clone $consultaEmpresa)->select('ubicacion')->selectRaw('COUNT(*) as total')->groupBy('ubicacion')->orderByDesc('total')->get(),
            'recentEvents' => EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->with('campaign:id,nombre,titulo')->latest('creado_en')->limit(5)->get(['id', 'campana_id', 'tipo', 'creado_en']),
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarCampaniaRequest $request, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion): RedirectResponse
    {
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para registrar campañas.');
        $data = $request->validated();
        $segmentIds = $data['segmentos'] ?? [];
        $data = $this->campaignData($data, $request);

        $campaign = Campania::create($data + ['empresa_id' => $empresaId, 'activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.created', 'campanas_publicitarias', $campaign->id, null, $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en']), $request->ip());

        return back()->with('status', 'Campaña creada.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $campaign  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(Campania $campaign, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion): RedirectResponse
    {
        abort_unless($campaign->empresa_id !== null && (int) $campaign->empresa_id === $autorizacion->activeCompanyId(), 404);
        $campaign->update(['activo' => false, 'actualizado_en' => now()]);
        $audit->record(request()->user()->id, 'campaign.deactivated', 'campanas_publicitarias', $campaign->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Campaña desactivada.');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, ServicioAutorizacion $autorizacion, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        $format = $request->string('format', 'csv')->toString();
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para exportar campañas.');
        $filters = $this->filters($request);
        $campaigns = $this->campaignQuery($filters, $empresaId)->with('segments')->orderBy('id')->limit(5000)->get();
        $rows = $campaigns->map(fn (Campania $campaign): array => [
            $campaign->nombre,
            $campaign->titulo,
            $campaign->activo ? 'Activa' : 'Inactiva',
            $campaign->ubicacion,
            (int) $campaign->vistas,
            (int) $campaign->clics,
            $campaign->vistas > 0 ? round(((int) $campaign->clics / (int) $campaign->vistas) * 100, 1).'%' : '0.0%',
            $campaign->inicia_en?->format('Y-m-d H:i'),
            $campaign->finaliza_en?->format('Y-m-d H:i'),
            $campaign->segments->pluck('nombre')->implode(', '),
        ])->all();
        $response = $exporter->download($format, 'campanas-'.now()->format('Ymd'), [
            'Campaña', 'Título', 'Estado', 'Ubicación', 'Vistas', 'Clics', 'CTR', 'Inicio', 'Fin', 'Segmentos',
        ], $rows, [
            'title' => 'REPORTE DE CAMPAÑAS',
            'subtitle' => 'Campañas reales de la empresa activa.',
            'module' => 'Ecommerce / Marketing',
            'report_name' => 'Campañas',
            'filters' => $filters,
            'generated_by' => (string) $request->user()->nombre,
            'role' => 'Usuario autorizado',
            'scope' => 'Empresa activa y filtros RBAC vigentes.',
            'indicators' => [['label' => 'Campañas incluidas', 'value' => count($rows)]],
        ]);
        $audit->record($request->user()->id, 'exportar', 'campanas_publicitarias', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $campaign  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarCampaniaRequest $request, Campania $campaign, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion): RedirectResponse
    {
        abort_unless($campaign->empresa_id !== null && (int) $campaign->empresa_id === $autorizacion->activeCompanyId(), 404);
        $data = $request->validated();
        $segmentIds = $data['segmentos'] ?? [];
        $data = $this->campaignData($data, $request);
        $before = $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en', 'activo']);
        $campaign->update($data + ['actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.updated', 'campanas_publicitarias', $campaign->id, $before, $campaign->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Campaña actualizada.');
    }

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function import(ImportarCampaniasRequest $request, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion): RedirectResponse
    {
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para importar campañas.');

        $expected = ['nombre', 'ubicacion', 'titulo', 'descripcion', 'texto_boton', 'url_boton', 'inicia_en', 'finaliza_en', 'activo', 'segmentos'];
        $handle = fopen($request->file('archivo')->getRealPath(), 'rb');
        abort_unless($handle !== false, 422, 'No se pudo leer el archivo CSV.');
        $header = fgetcsv($handle);
        $header = is_array($header) ? array_map(static fn ($value): string => trim((string) $value), $header) : [];
        if ($header !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages(['archivo' => 'El CSV debe contener exactamente las columnas: '.implode(',', $expected).'.']);
        }

        $created = 0;
        try {
            DB::transaction(function () use ($handle, $empresaId, $expected, $audit, $request, &$created): void {
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) === 1 && trim((string) $row[0]) === '') {
                        continue;
                    }
                    if (count($row) !== count($expected)) {
                        throw ValidationException::withMessages(['archivo' => 'El CSV contiene una fila con cantidad de columnas inválida.']);
                    }
                    $data = array_combine($expected, array_map(static fn ($value): string => trim((string) $value), $row));
                    $validator = validator($data, [
                        'nombre' => ['required', 'string', 'max:120'],
                        'ubicacion' => ['required', 'in:emergente,lateral,barra_superior'],
                        'titulo' => ['required', 'string', 'max:160'],
                        'descripcion' => ['nullable', 'string', 'max:500'],
                        'texto_boton' => ['required', 'string', 'max:80'],
                        'url_boton' => ['required', 'string', 'max:255'],
                        'inicia_en' => ['required', 'date'],
                        'finaliza_en' => ['required', 'date', 'after:inicia_en'],
                        'activo' => ['required', 'in:0,1'],
                    ]);
                    if ($validator->fails()) {
                        throw ValidationException::withMessages(['archivo' => 'Fila inválida: '.implode(' ', $validator->errors()->all())]);
                    }
                    $segmentIds = collect(explode('|', $data['segmentos'] ?? ''))
                        ->filter(static fn (string $id): bool => $id !== '')
                        ->map(static fn (string $id): int => (int) $id)->values()->all();
                    if (count($segmentIds) !== count(array_unique($segmentIds))) {
                        throw ValidationException::withMessages(['archivo' => 'Una fila contiene segmentos repetidos.']);
                    }
                    if ($segmentIds !== [] && DB::table('segmentos_clientes')->whereIn('id', $segmentIds)->where('activo', true)->count() !== count($segmentIds)) {
                        throw ValidationException::withMessages(['archivo' => 'Una fila contiene segmentos inexistentes o inactivos.']);
                    }
                    $campaign = Campania::create([
                        'empresa_id' => $empresaId,
                        'nombre' => $data['nombre'],
                        'ubicacion' => $data['ubicacion'],
                        'titulo' => $data['titulo'],
                        'descripcion' => $data['descripcion'] ?: null,
                        'texto_boton' => $data['texto_boton'],
                        'url_boton' => $data['url_boton'],
                        'inicia_en' => Carbon::parse($data['inicia_en']),
                        'finaliza_en' => Carbon::parse($data['finaliza_en']),
                        'activo' => $data['activo'] === '1',
                        'vistas' => 0,
                        'clics' => 0,
                        'creado_en' => now(),
                        'actualizado_en' => now(),
                    ]);
                    $campaign->segments()->sync($segmentIds);
                    $audit->record($request->user()->id, 'campaign.created.import', 'campanas_publicitarias', $campaign->id, null, $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en']), $request->ip());
                    $created++;
                }
            });
        } finally {
            fclose($handle);
        }

        if ($created === 0) {
            throw ValidationException::withMessages(['archivo' => 'El CSV no contiene filas de campañas.']);
        }

        return back()->with('status', $created.' campaña(s) importada(s) correctamente.');
    }

    /**
     * @param  array{buscar:?string,ubicacion:?string,estado:?string,por_pagina:int}  $filters
     * @return Builder<Campania>
     */
    private function campaignQuery(array $filters, int $empresaId): Builder
    {
        return Campania::query()->where('empresa_id', $empresaId)
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('titulo', 'like', '%'.$search.'%')
                        ->orWhere('descripcion', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['ubicacion'], fn (Builder $query, string $location): Builder => $query->where('ubicacion', $location))
            ->when($filters['estado'] === 'activa', fn (Builder $query): Builder => $query->where('activo', true))
            ->when($filters['estado'] === 'inactiva', fn (Builder $query): Builder => $query->where('activo', false));
    }

    /**
     * @return array{buscar:?string,ubicacion:?string,estado:?string,por_pagina:int}
     */
    private function filters(Request $request): array
    {
        $search = trim($request->string('buscar')->toString());
        $location = trim($request->string('ubicacion')->toString());
        $status = trim($request->string('estado')->toString());
        $perPage = $request->integer('por_pagina', 10);

        return [
            'buscar' => $search !== '' ? $search : null,
            'ubicacion' => in_array($location, ['emergente', 'lateral', 'barra_superior'], true) ? $location : null,
            'estado' => in_array($status, ['activa', 'inactiva'], true) ? $status : null,
            'por_pagina' => in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function campaignData(array $data, GuardarCampaniaRequest $request): array
    {
        unset($data['segmentos'], $data['imagen']);

        if ($request->hasFile('imagen')) {
            $data['url_imagen'] = 'storage/'.$request->file('imagen')->store('campanas', 'public');
        }

        return $data;
    }

    /**
     * Ejecuta la operación trendPercent definida por la clase CampaniaController.
     *
     * @param  mixed  $current  Parámetro recibido por la operación.
     * @param  mixed  $previous  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
