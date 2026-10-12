<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductManagementController extends Controller
{
    public function index(): View
    {
        return view('management.products', [
            'products' => Product::with('category')->orderBy('id')->paginate(30),
            'categories' => Category::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreProductRequest $request, AuditLogger $audit): RedirectResponse
    {
        $data = $this->attributes($request->validated());
        $product = Product::create($data + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'product.created', 'productos', $product->id, null, $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'existencias']), $request->ip());

        return back()->with('status', 'Producto guardado.');
    }

    public function update(UpdateProductRequest $request, Product $product, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('update', $product);
        $before = $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'precio_oferta', 'existencias']);
        $product->update($this->attributes($request->validated()));
        $audit->record($request->user()->id, 'product.updated', 'productos', $product->id, $before, $product->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Producto actualizado.');
    }

    public function deactivate(Product $product, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->update(['activo' => false]);
        $audit->record(request()->user()->id, 'product.deactivated', 'productos', $product->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Producto desactivado; su historial comercial se conserva.');
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        $category = Category::findOrFail($data['categoria_id']);
        $data['categoria'] = $category->nombre;

        return $data;
    }
}
