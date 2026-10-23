<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarMensajeContactoRequest;
use App\Servicios\ServicioContacto;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaginaController extends Controller
{
    public function __construct(private readonly ServicioContacto $contactService) {}

    public function about(): View
    {
        return view('publico.paginas.nosotros');
    }

    public function contact(): View
    {
        return view('publico.paginas.contacto');
    }

    public function sendContact(GuardarMensajeContactoRequest $request): RedirectResponse
    {
        $this->contactService->register($request->validated());

        return back()->with('status', 'Mensaje recibido. Nos pondremos en contacto contigo.');
    }
}
