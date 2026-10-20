<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('management.suppliers', ['suppliers' => Supplier::query()->orderBy('nombre')->paginate(30)]);
    }

    public function store(StoreSupplierRequest $request, AuditLogger $audit): RedirectResponse
    {
        $supplier = Supplier::create($request->validated() + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'supplier.created', 'proveedores', $supplier->id, null, $supplier->only(['nombre', 'ruc', 'correo', 'ciudad']), $request->ip());

        return back()->with('status', 'Proveedor registrado.');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier, AuditLogger $audit): RedirectResponse
    {
        $before = $supplier->only(['nombre', 'ruc', 'correo', 'telefono', 'ciudad', 'activo']);
        $supplier->update($request->validated());
        $audit->record($request->user()->id, 'supplier.updated', 'proveedores', $supplier->id, $before, $supplier->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Proveedor actualizado.');
    }

    public function deactivate(Supplier $supplier, AuditLogger $audit): RedirectResponse
    {
        $supplier->update(['activo' => false]);
        $audit->record(request()->user()->id, 'supplier.deactivated', 'proveedores', $supplier->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Proveedor desactivado.');
    }
    
public function activate(Supplier $supplier, AuditLogger $audit): RedirectResponse
{
    if ($supplier->activo) {
        return back()->with('status', 'El proveedor ya está activo.');
    }

    $supplier->update(['activo' => true]);

    $audit->record(
        request()->user()->id,
        'supplier.activated',
        'proveedores',
        $supplier->id,
        ['activo' => false],
        ['activo' => true],
        request()->ip()
    );

    return back()->with('status', 'Proveedor activado correctamente.');
}

}
