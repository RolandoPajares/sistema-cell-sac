<?php

namespace App\Http\Controllers\Gestion\Productos;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarProductoRequest;
use App\Http\Requests\GuardarProductoRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GestionProductosController extends Controller
{
    public function index(): View
    {
        return view('gestion.productos.listar', [
            'products' => Producto::with('category')->orderBy('id')->paginate(30),
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(GuardarProductoRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $this->attributes($request->validated());
        $product = Producto::create($data + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'product.created', 'productos', $product->id, null, $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'existencias']), $request->ip());

        return back()->with('status', 'Producto guardado.');
    }

    public function update(ActualizarProductoRequest $request, Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        $this->authorize('update', $product);
        $before = $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'precio_oferta', 'existencias']);
        $product->update($this->attributes($request->validated()));
        $audit->record($request->user()->id, 'product.updated', 'productos', $product->id, $before, $product->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Producto actualizado.');
    }

    public function deactivate(Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->update(['activo' => false]);
        $audit->record(request()->user()->id, 'product.deactivated', 'productos', $product->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Producto desactivado; su historial comercial se conserva.');
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        $category = Categoria::findOrFail($data['categoria_id']);
        $data['categoria'] = $category->nombre;

        return $data;
    }
}
