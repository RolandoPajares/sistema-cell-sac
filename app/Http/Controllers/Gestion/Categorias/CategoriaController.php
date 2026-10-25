<?php

namespace App\Http\Controllers\Gestion\Categorias;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCategoriaRequest;
use App\Http\Requests\GuardarCategoriaRequest;
use App\Models\Catalogo\Categoria;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        return view('gestion.categorias.listar', [
            'categories' => Categoria::query()->withCount('products')->orderBy('nombre')->paginate(30),
        ]);
    }

    public function store(GuardarCategoriaRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $category = Categoria::create($request->validated() + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'category.created', 'categorias', $category->id, null, $category->only(['nombre', 'descripcion']), $request->ip());

        return back()->with('status', 'CategorÃ­a registrada.');
    }

    public function update(ActualizarCategoriaRequest $request, Categoria $category, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $category->only(['nombre', 'descripcion', 'activo']);
        $category->update($request->validated());
        $audit->record($request->user()->id, 'category.updated', 'categorias', $category->id, $before, $category->only(array_keys($before)), $request->ip());

        return back()->with('status', 'CategorÃ­a actualizada.');
    }

    public function deactivate(Categoria $category, RegistradorAuditoria $audit): RedirectResponse
    {
        $category->update(['activo' => false]);
        $audit->record(request()->user()->id, 'category.deactivated', 'categorias', $category->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'CategorÃ­a desactivada.');
    }
}
