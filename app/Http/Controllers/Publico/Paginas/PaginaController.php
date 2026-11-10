<?php

namespace App\Http\Controllers\Publico\Paginas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\Paginas\GuardarMensajeContactoRequest;
use App\Servicios\Publico\Paginas\ServicioContacto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase PaginaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class PaginaController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $contactService  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioContacto $contactService) {}

    /**
     * Ejecuta la operación about definida por la clase PaginaController.
     *
     * @return View Resultado de la operación.
     */
    public function about(): View
    {
        return view('publico.paginas.nosotros');
    }

    /**
     * Ejecuta la operación contact definida por la clase PaginaController.
     *
     * @return View Resultado de la operación.
     */
    public function contact(Request $request): View
    {
        $user = $request->user();

        return view('publico.paginas.contacto', [
            'contactDefaults' => [
                'nombre' => $user?->nombre,
                'contacto' => $user?->correo,
            ],
        ]);
    }

    /**
     * Muestra el libro de reclamaciones informativo de la tienda.
     */
    public function claimsBook(): View
    {
        return view('publico.paginas.libro-reclamaciones');
    }

    /**
     * Muestra la política de privacidad informativa de la tienda.
     */
    public function privacyPolicy(): View
    {
        return view('publico.paginas.politicas-privacidad');
    }

    /**
     * Muestra los términos y condiciones informativos de la tienda.
     */
    public function terms(): View
    {
        return view('publico.paginas.terminos-condiciones');
    }

    /**
     * Muestra las preguntas frecuentes de la tienda.
     */
    public function frequentlyAskedQuestions(): View
    {
        return view('publico.paginas.preguntas-frecuentes');
    }

    /**
     * Ejecuta la operación sendContact definida por la clase PaginaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function sendContact(GuardarMensajeContactoRequest $request): RedirectResponse
    {
        $this->contactService->register($request->validated());

        return back()->with('status', 'Mensaje recibido. Nos pondremos en contacto contigo.');
    }
}
