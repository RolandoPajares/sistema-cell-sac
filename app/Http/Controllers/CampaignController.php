<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Models\CustomerSegment;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        return view('management.campaigns', [
            'campaigns' => Campaign::with('segments')->latest('inicia_en')->latest('id')->paginate(30),
            'segments' => CustomerSegment::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreCampaignRequest $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validated();
        $segmentIds = $data['segmentos'] ?? [];
        unset($data['segmentos']);

        $campaign = Campaign::create($data + ['activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.created', 'campanas_publicitarias', $campaign->id, null, $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en']), $request->ip());

        return back()->with('status', 'Campaña creada.');
    }

    public function deactivate(Campaign $campaign, AuditLogger $audit): RedirectResponse
    {
        $campaign->update(['activo' => false, 'actualizado_en' => now()]);
        $audit->record(request()->user()->id, 'campaign.deactivated', 'campanas_publicitarias', $campaign->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Campaña desactivada.');
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validated();
        $segmentIds = $data['segmentos'] ?? [];
        unset($data['segmentos']);
        $before = $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en', 'activo']);
        $campaign->update($data + ['actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.updated', 'campanas_publicitarias', $campaign->id, $before, $campaign->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Campaña actualizada.');
    }
    
public function activate(Campaign $campaign, AuditLogger $audit): RedirectResponse
{
    if ($campaign->activo) {
        return back()->with('status', 'La campaña ya está activa.');
    }

    $campaign->update([
        'activo' => true,
        'actualizado_en' => now(),
    ]);

    $audit->record(
        request()->user()->id,
        'campaign.activated',
        'campanas_publicitarias',
        $campaign->id,
        ['activo' => false],
        ['activo' => true],
        request()->ip()
    );

    return back()->with('status', 'Campaña activada correctamente.');
}

}
