<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Marketing\Campania;
use App\Models\Marketing\EventoCampania;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Define la responsabilidad de la clase CampaniaPublicaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CampaniaPublicaController extends Controller
{
    /**
     * Ejecuta la operación track definida por la clase CampaniaPublicaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $campaign  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function track(Request $request, Campania $campaign): RedirectResponse
    {
        $type = $request->validate(['tipo' => ['required', 'in:vista,clic']])['tipo'];
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($campaign->activo && $campaign->inicia_en <= now() && $campaign->finaliza_en >= now(), 404);

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $campaign, $type): void {
            // Guarda en $sessionHash el resultado necesario para continuar esta operacion.
            $sessionHash = hash_hmac('sha256', $request->session()->getId(), (string) config('app.key'));
            // Prepara en $alreadyTracked la consulta que obtiene o modifica los registros necesarios.
            $alreadyTracked = EventoCampania::query()->where('campana_id', $campaign->id)->where('tipo', $type)
                ->where('sesion_hash', $sessionHash)->whereDate('creado_en', now()->toDateString())->exists();

            // Comprueba la condicion $alreadyTracked antes de ejecutar este bloque.
            if ($alreadyTracked) {
                return;
            }

            EventoCampania::create([
                'campana_id' => $campaign->id,
                'usuario_id' => $request->user()?->id,
                'tipo' => $type,
                'sesion_hash' => $sessionHash,
                'creado_en' => now(),
            ]);
            $campaign->increment($type === 'clic' ? 'clics' : 'vistas');
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return redirect()->route('catalog.index')->with('status', $type === 'clic' ? 'Campaña registrada.' : null);
    }
}
