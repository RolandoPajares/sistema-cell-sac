<?php

namespace App\Http\Controllers\Gestion\EcommerceMarketing\Campanas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\EcommerceMarketing\Campanas\ActualizarCampaniaRequest;
use App\Http\Requests\Gestion\EcommerceMarketing\Campanas\GuardarCampaniaRequest;
use App\Http\Requests\Gestion\EcommerceMarketing\Campanas\ImportarCampaniasRequest;
use App\Models\Marketing\Campania;
use App\Models\Marketing\EventoCampania;
use App\Models\Marketing\SegmentoCliente;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para consultar campañas.');
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        // Prepara en $consultaEmpresa la consulta que obtiene o modifica los registros necesarios.
        $consultaEmpresa = Campania::query()->where('empresa_id', $empresaId);
        // Guarda en $campaigns el resultado necesario para continuar esta operacion.
        $campaigns = $this->consultaCampanias($filters, $empresaId)
            ->with('segments')
            ->withCount('events')
            ->orderByDesc('vistas')
            ->orderByDesc('id')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        // Guarda en $totalViews el resultado necesario para continuar esta operacion.
        $totalViews = (int) (clone $consultaEmpresa)->sum('vistas');
        // Guarda en $totalClicks el resultado necesario para continuar esta operacion.
        $totalClicks = (int) (clone $consultaEmpresa)->sum('clics');
        // Agrupa en $currentMonth los datos que se utilizaran en la siguiente operacion.
        $currentMonth = [now()->startOfMonth(), now()->endOfMonth()];
        // Agrupa en $previousMonth los datos que se utilizaran en la siguiente operacion.
        $previousMonth = [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()];
        $currentViews = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'vista')->whereBetween('creado_en', $currentMonth)->count();
        $previousViews = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'vista')->whereBetween('creado_en', $previousMonth)->count();
        $currentClicks = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'clic')->whereBetween('creado_en', $currentMonth)->count();
        $previousClicks = (int) EventoCampania::query()->whereHas('campaign', static fn (Builder $consulta): Builder => $consulta->where('empresa_id', $empresaId))->where('tipo', 'clic')->whereBetween('creado_en', $previousMonth)->count();

        // Devuelve la vista 'gestion.ecommerce-marketing.campanas.listar' con los datos preparados por este controlador.
        return view('gestion.ecommerce-marketing.campanas.listar', [
            'campaigns' => $campaigns,
            'segments' => SegmentoCliente::query()->where('activo', true)->orderBy('nombre')->get(),
            'filters' => $filters,
            'totalCampaigns' => (clone $consultaEmpresa)->count(),
            'activeCampaigns' => (clone $consultaEmpresa)->where('activo', true)->count(),
            'totalViews' => $totalViews,
            'totalClicks' => $totalClicks,
            'conversionRate' => $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 1) : 0,
            'viewsTrend' => $this->porcentajeTendencia($currentViews, $previousViews),
            'clicksTrend' => $this->porcentajeTendencia($currentClicks, $previousClicks),
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
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para registrar campañas.');
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        // Guarda en $segmentIds el resultado necesario para continuar esta operacion.
        $segmentIds = $data['segmentos'] ?? [];
        // Guarda en $data el resultado necesario para continuar esta operacion.
        $data = $this->datosCampania($data, $request);

        $campaign = Campania::create($data + ['empresa_id' => $empresaId, 'activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.created', 'campanas_publicitarias', $campaign->id, null, $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en']), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($campaign->empresa_id !== null && (int) $campaign->empresa_id === $autorizacion->activeCompanyId(), 404);
        $campaign->update(['activo' => false, 'actualizado_en' => now()]);
        $audit->record(request()->user()->id, 'campaign.deactivated', 'campanas_publicitarias', $campaign->id, ['activo' => true], ['activo' => false], request()->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Guarda en $format el resultado necesario para continuar esta operacion.
        $format = $request->string('format', 'csv')->toString();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para exportar campañas.');
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        // Guarda en $campaigns el resultado necesario para continuar esta operacion.
        $campaigns = $this->consultaCampanias($filters, $empresaId)->with('segments')->orderBy('id')->limit(5000)->get();
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
        // Guarda en $response el resultado necesario para continuar esta operacion.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($campaign->empresa_id !== null && (int) $campaign->empresa_id === $autorizacion->activeCompanyId(), 404);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        // Guarda en $segmentIds el resultado necesario para continuar esta operacion.
        $segmentIds = $data['segmentos'] ?? [];
        // Guarda en $data el resultado necesario para continuar esta operacion.
        $data = $this->datosCampania($data, $request);
        // Guarda en $before el resultado necesario para continuar esta operacion.
        $before = $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en', 'activo']);
        $campaign->update($data + ['actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.updated', 'campanas_publicitarias', $campaign->id, $before, $campaign->only(array_keys($before)), $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Guarda en $empresaId el resultado necesario para continuar esta operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para importar campañas.');

        // Agrupa en $expected los datos que se utilizaran en la siguiente operacion.
        $expected = ['nombre', 'ubicacion', 'titulo', 'descripcion', 'texto_boton', 'url_boton', 'inicia_en', 'finaliza_en', 'activo', 'segmentos'];
        // Guarda en $handle el resultado necesario para continuar esta operacion.
        $handle = fopen($request->file('archivo')->getRealPath(), 'rb');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($handle !== false, 422, 'No se pudo leer el archivo CSV.');
        // Guarda en $header el resultado necesario para continuar esta operacion.
        $header = fgetcsv($handle);
        $header = is_array($header) ? array_map(static fn ($value): string => trim((string) $value), $header) : [];
        // Comprueba la condicion $header !== $expected antes de ejecutar este bloque.
        if ($header !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages(['archivo' => 'El CSV debe contener exactamente las columnas: '.implode(',', $expected).'.']);
        }

        // Guarda en $created el resultado necesario para continuar esta operacion.
        $created = 0;
        // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
        try {
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::transaction(function () use ($handle, $empresaId, $expected, $audit, $request, &$created): void {
                while (($row = fgetcsv($handle)) !== false) {
                    // Comprueba la condicion count($row) === 1 && trim((string) $row[0]) === '' antes de ejecutar este bloque.
                    if (count($row) === 1 && trim((string) $row[0]) === '') {
                        continue;
                    }
                    // Comprueba la condicion count($row) !== count($expected) antes de ejecutar este bloque.
                    if (count($row) !== count($expected)) {
                        throw ValidationException::withMessages(['archivo' => 'El CSV contiene una fila con cantidad de columnas inválida.']);
                    }
                    $data = array_combine($expected, array_map(static fn ($value): string => trim((string) $value), $row));
                    // Guarda en $validator el resultado necesario para continuar esta operacion.
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
                    // Comprueba la condicion $validator->fails() antes de ejecutar este bloque.
                    if ($validator->fails()) {
                        throw ValidationException::withMessages(['archivo' => 'Fila inválida: '.implode(' ', $validator->errors()->all())]);
                    }
                    // Guarda en $segmentIds el resultado necesario para continuar esta operacion.
                    $segmentIds = collect(explode('|', $data['segmentos'] ?? ''))
                        ->filter(static fn (string $id): bool => $id !== '')
                        ->map(static fn (string $id): int => (int) $id)->values()->all();
                    // Comprueba la condicion count($segmentIds) !== count(array_unique($segmentIds)) antes de ejecutar este bloque.
                    if (count($segmentIds) !== count(array_unique($segmentIds))) {
                        throw ValidationException::withMessages(['archivo' => 'Una fila contiene segmentos repetidos.']);
                    }
                    // Comprueba la condicion $segmentIds !== [] && DB::table('segmentos_clientes')->whereIn('id', $segmentIds)->where('activo', true)->count() !== count($segmentIds) antes de ejecutar este bloque.
                    if ($segmentIds !== [] && DB::table('segmentos_clientes')->whereIn('id', $segmentIds)->where('activo', true)->count() !== count($segmentIds)) {
                        throw ValidationException::withMessages(['archivo' => 'Una fila contiene segmentos inexistentes o inactivos.']);
                    }
                    // Crea y guarda en $campaign el registro correspondiente mediante el modelo.
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

        // Comprueba la condicion $created === 0 antes de ejecutar este bloque.
        if ($created === 0) {
            throw ValidationException::withMessages(['archivo' => 'El CSV no contiene filas de campañas.']);
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', $created.' campaña(s) importada(s) correctamente.');
    }

    /**
     * @param  array{buscar:?string,ubicacion:?string,estado:?string,por_pagina:int}  $filters
     * @return Builder<Campania>
     */
    private function consultaCampanias(array $filters, int $empresaId): Builder
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
    private function filtros(Request $request): array
    {
        // Guarda en $search el resultado necesario para continuar esta operacion.
        $search = trim($request->string('buscar')->toString());
        // Guarda en $location el resultado necesario para continuar esta operacion.
        $location = trim($request->string('ubicacion')->toString());
        // Guarda en $status el resultado necesario para continuar esta operacion.
        $status = trim($request->string('estado')->toString());
        // Guarda en $perPage el resultado necesario para continuar esta operacion.
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
    private function datosCampania(array $data, GuardarCampaniaRequest $request): array
    {
        unset($data['segmentos'], $data['imagen']);

        // Comprueba la condicion $request->hasFile('imagen') antes de ejecutar este bloque.
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
    private function porcentajeTendencia(int $current, int $previous): ?float
    {
        // Comprueba la condicion $previous === 0 antes de ejecutar este bloque.
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
