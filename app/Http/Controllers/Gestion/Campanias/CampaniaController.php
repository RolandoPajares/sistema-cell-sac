<?php

namespace App\Http\Controllers\Gestion\Campanias;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCampaniaRequest;
use App\Http\Requests\GuardarCampaniaRequest;
use App\Models\Marketing\Campania;
use App\Models\Marketing\SegmentoCliente;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CampaniaController extends Controller
{
    public function index(): View
    {
        return view('gestion.campanias.listar', [
            'campaigns' => Campania::with('segments')->latest('inicia_en')->latest('id')->paginate(30),
            'segments' => SegmentoCliente::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(GuardarCampaniaRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $segmentIds = $data['segmentos'] ?? [];
        unset($data['segmentos']);

        $campaign = Campania::create($data + ['activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.created', 'campanas_publicitarias', $campaign->id, null, $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en']), $request->ip());

        return back()->with('status', 'CampaÃ±a creada.');
    }

    public function deactivate(Campania $campaign, RegistradorAuditoria $audit): RedirectResponse
    {
        $campaign->update(['activo' => false, 'actualizado_en' => now()]);
        $audit->record(request()->user()->id, 'campaign.deactivated', 'campanas_publicitarias', $campaign->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'CampaÃ±a desactivada.');
    }

    public function update(ActualizarCampaniaRequest $request, Campania $campaign, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $segmentIds = $data['segmentos'] ?? [];
        unset($data['segmentos']);
        $before = $campaign->only(['nombre', 'titulo', 'ubicacion', 'inicia_en', 'finaliza_en', 'activo']);
        $campaign->update($data + ['actualizado_en' => now()]);
        $campaign->segments()->sync($segmentIds);
        $audit->record($request->user()->id, 'campaign.updated', 'campanas_publicitarias', $campaign->id, $before, $campaign->only(array_keys($before)), $request->ip());

        return back()->with('status', 'CampaÃ±a actualizada.');
    }
}
