<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('management.categories', [
            'categories' => Category::query()->withCount('products')->orderBy('nombre')->paginate(30),
        ]);
    }

    public function store(StoreCategoryRequest $request, AuditLogger $audit): RedirectResponse
    {
        $category = Category::create($request->validated() + ['activo' => true, 'creado_en' => now()]);
        $audit->record($request->user()->id, 'category.created', 'categorias', $category->id, null, $category->only(['nombre', 'descripcion']), $request->ip());

        return back()->with('status', 'Categoría registrada.');
    }

    public function update(UpdateCategoryRequest $request, Category $category, AuditLogger $audit): RedirectResponse
    {
        $before = $category->only(['nombre', 'descripcion', 'activo']);
        $category->update($request->validated());
        $audit->record($request->user()->id, 'category.updated', 'categorias', $category->id, $before, $category->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Categoría actualizada.');
    }

    public function deactivate(Category $category, AuditLogger $audit): RedirectResponse
    {
        $category->update(['activo' => false]);
        $audit->record(request()->user()->id, 'category.deactivated', 'categorias', $category->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Categoría desactivada.');
    }
}
