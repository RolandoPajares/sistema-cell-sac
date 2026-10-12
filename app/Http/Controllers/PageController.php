<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly ContactService $contactService) {}

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function sendContact(StoreContactMessageRequest $request): RedirectResponse
    {
        $this->contactService->register($request->validated());

        return back()->with('status', 'Mensaje recibido. Nos pondremos en contacto contigo.');
    }
}
