<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicCampaignController extends Controller
{
    public function track(Request $request, Campaign $campaign): RedirectResponse
    {
        $type = $request->validate(['tipo' => ['required', 'in:vista,clic']])['tipo'];
        abort_unless($campaign->activo && $campaign->inicia_en <= now() && $campaign->finaliza_en >= now(), 404);

        DB::transaction(function () use ($request, $campaign, $type): void {
            $sessionHash = hash_hmac('sha256', $request->session()->getId(), (string) config('app.key'));
            $alreadyTracked = CampaignEvent::query()->where('campana_id', $campaign->id)->where('tipo', $type)
                ->where('sesion_hash', $sessionHash)->whereDate('creado_en', now()->toDateString())->exists();

            if ($alreadyTracked) {
                return;
            }

            CampaignEvent::create([
                'campana_id' => $campaign->id,
                'usuario_id' => $request->user()?->id,
                'tipo' => $type,
                'sesion_hash' => $sessionHash,
                'creado_en' => now(),
            ]);
            $campaign->increment($type === 'clic' ? 'clics' : 'vistas');
        });

        return redirect()->route('catalog.index')->with('status', $type === 'clic' ? 'Campaña registrada.' : null);
    }
}
