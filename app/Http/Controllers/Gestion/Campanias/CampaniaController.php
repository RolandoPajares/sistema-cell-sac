<?php

namespace App\Http\Controllers\Gestion\Campanias;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCampaniaRequest;
use App\Http\Requests\GuardarCampaniaRequest;
use App\Models\Marketing\Campania;
use App\Models\Marketing\EventoCampania;
use App\Models\Marketing\SegmentoCliente;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaniaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $campaigns = $this->campaignQuery($filters)
            ->with('segments')
            ->withCount('events')
            ->orderByDesc('vistas')
            ->orderByDesc('id')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $totalViews = (int) Campania::query()->sum('vistas');
        $totalClicks = (int) Campania::query()->sum('clics');
        $currentMonth = [now()->startOfMonth(), now()->endOfMonth()];
        $previousMonth = [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()];
        $currentViews = (int) EventoCampania::query()->where('tipo', 'vista')->whereBetween('creado_en', $currentMonth)->count();
        $previousViews = (int) EventoCampania::query()->where('tipo', 'vista')->whereBetween('creado_en', $previousMonth)->count();
        $currentClicks = (int) EventoCampania::query()->where('tipo', 'clic')->whereBetween('creado_en', $currentMonth)->count();
        $previousClicks = (int) EventoCampania::query()->where('tipo', 'clic')->whereBetween('creado_en', $previousMonth)->count();

        return view('gestion.campanias.listar', [
            'campaigns' => $campaigns,
            'segments' => SegmentoCliente::query()->where('activo', true)->orderBy('nombre')->get(),
            'filters' => $filters,
            'totalCampaigns' => Campania::query()->count(),
            'activeCampaigns' => Campania::query()->where('activo', true)->count(),
            'totalViews' => $totalViews,
            'totalClicks' => $totalClicks,
            'conversionRate' => $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 1) : 0,
            'viewsTrend' => $this->trendPercent($currentViews, $previousViews),
            'clicksTrend' => $this->trendPercent($currentClicks, $previousClicks),
            'topCampaigns' => Campania::query()->with('segments')->orderByDesc('vistas')->orderByDesc('clics')->limit(5)->get(),
            'distribution' => Campania::query()->select('ubicacion')->selectRaw('COUNT(*) as total')->groupBy('ubicacion')->orderByDesc('total')->get(),
            'recentEvents' => EventoCampania::query()->with('campaign:id,nombre,titulo')->latest('creado_en')->limit(5)->get(['id', 'campana_id', 'tipo', 'creado_en']),
        ]);
    }

    public function store(GuardarCampaniaRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $segmentIds = $data['segmentos'] ?? [];
        $data = $this->campaignData($data, $request);

        $campaign = Campania::create($data + ['activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.created', 'campanas_publicitarias', $campaign->id, null, $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en']), $request->ip());

        return back()->with('status', 'Campaña creada.');
    }

    public function deactivate(Campania $campaign, RegistradorAuditoria $audit): RedirectResponse
    {
        $campaign->update(['activo' => false, 'actualizado_en' => now()]);
        $audit->record(request()->user()->id, 'campaign.deactivated', 'campanas_publicitarias', $campaign->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Campaña desactivada.');
    }

    public function update(ActualizarCampaniaRequest $request, Campania $campaign, RegistradorAuditoria $audit): RedirectResponse
    {
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
     * @param  array{buscar:?string,ubicacion:?string,estado:?string,por_pagina:int}  $filters
     * @return Builder<Campania>
     */
    private function campaignQuery(array $filters): Builder
    {
        return Campania::query()
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

    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
