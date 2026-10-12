<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('management.customers', [
            'customers' => Customer::query()->with('user')->orderBy('nombre_contacto')->paginate(30),
        ]);
    }

    public function store(StoreCustomerRequest $request, AuditLogger $audit): RedirectResponse
    {
        $customer = Customer::create($request->validated() + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'customer.created', 'clientes', $customer->id, null, $customer->only(['tipo', 'documento', 'nombre_contacto', 'correo']), $request->ip());

        return back()->with('status', 'Cliente registrado.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer, AuditLogger $audit): RedirectResponse
    {
        $before = $customer->only(['tipo', 'documento', 'nombre_contacto', 'correo', 'activo']);
        $customer->update($request->validated());
        $audit->record($request->user()->id, 'customer.updated', 'clientes', $customer->id, $before, $customer->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Cliente actualizado.');
    }

    public function deactivate(Customer $customer, AuditLogger $audit): RedirectResponse
    {
        $customer->update(['activo' => false]);
        $audit->record(request()->user()->id, 'customer.deactivated', 'clientes', $customer->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Cliente desactivado.');
    }
}
