<?php

namespace App\Http\Controllers\Gestion\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarProveedorRequest;
use App\Http\Requests\GuardarProveedorRequest;
use App\Models\Compras\Proveedor;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(): View
    {
        return view('gestion.proveedores.listar', ['suppliers' => Proveedor::query()->orderBy('nombre')->paginate(30)]);
    }

    public function store(GuardarProveedorRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $supplier = Proveedor::create($request->validated() + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'supplier.created', 'proveedores', $supplier->id, null, $supplier->only(['nombre', 'ruc', 'correo', 'ciudad']), $request->ip());

        return back()->with('status', 'Proveedor registrado.');
    }

    public function update(ActualizarProveedorRequest $request, Proveedor $supplier, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $supplier->only(['nombre', 'ruc', 'correo', 'telefono', 'ciudad', 'activo']);
        $supplier->update($request->validated());
        $audit->record($request->user()->id, 'supplier.updated', 'proveedores', $supplier->id, $before, $supplier->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Proveedor actualizado.');
    }

    public function deactivate(Proveedor $supplier, RegistradorAuditoria $audit): RedirectResponse
    {
        $supplier->update(['activo' => false]);
        $audit->record(request()->user()->id, 'supplier.deactivated', 'proveedores', $supplier->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Proveedor desactivado.');
    }
}
