<?php

namespace App\Http\Controllers\Gestion\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarClienteRequest;
use App\Http\Requests\GuardarClienteRequest;
use App\Models\Clientes\Cliente;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        return view('gestion.clientes.listar', [
            'customers' => Cliente::query()->with('user')->orderBy('nombre_contacto')->paginate(30),
        ]);
    }

    public function store(GuardarClienteRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $customer = Cliente::create($request->validated() + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'customer.created', 'clientes', $customer->id, null, $customer->only(['tipo', 'documento', 'nombre_contacto', 'correo']), $request->ip());

        return back()->with('status', 'Cliente registrado.');
    }

    public function update(ActualizarClienteRequest $request, Cliente $customer, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $customer->only(['tipo', 'documento', 'nombre_contacto', 'correo', 'activo']);
        $customer->update($request->validated());
        $audit->record($request->user()->id, 'customer.updated', 'clientes', $customer->id, $before, $customer->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Cliente actualizado.');
    }

    public function deactivate(Cliente $customer, RegistradorAuditoria $audit): RedirectResponse
    {
        $customer->update(['activo' => false]);
        $audit->record(request()->user()->id, 'customer.deactivated', 'clientes', $customer->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Cliente desactivado.');
    }
}
