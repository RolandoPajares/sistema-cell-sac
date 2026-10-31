<?php

namespace App\Http\Controllers\Gestion\Categorias;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCategoriaRequest;
use App\Http\Requests\GuardarCategoriaRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('gestion.categorias.listar', [
            'categories' => $this->categoryQuery($filters)->paginate(7)->withQueryString(),
            'filters' => $filters,
            'totalCategories' => Categoria::query()->count(),
            'activeCategories' => Categoria::query()->where('activo', true)->count(),
            'inactiveCategories' => Categoria::query()->where('activo', false)->count(),
            'assignedProducts' => Producto::query()->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $categories = $this->categoryQuery($this->filters($request))->cursor();

        return response()->streamDownload(function () use ($categories): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['id', 'nombre', 'descripcion', 'productos', 'estado', 'fecha_creacion']);

            foreach ($categories as $category) {
                fputcsv($output, [
                    $category->id,
                    $category->nombre,
                    $category->descripcion,
                    $category->products_count,
                    $category->activo ? 'Activa' : 'Inactiva',
                    $category->creado_en?->format('d/m/Y H:i'),
                ]);
            }

            fclose($output);
        }, 'categorias.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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

    /**
     * @param  array{buscar:?string,estado:?string,orden:string}  $filters
     * @return Builder<Categoria>
     */
    private function categoryQuery(array $filters): Builder
    {
        return Categoria::query()
            ->withCount('products')
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('descripcion', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['estado'] === 'activa', fn (Builder $query): Builder => $query->where('activo', true))
            ->when($filters['estado'] === 'inactiva', fn (Builder $query): Builder => $query->where('activo', false))
            ->when($filters['orden'] === 'nombre_desc', fn (Builder $query): Builder => $query->orderByDesc('nombre'))
            ->when($filters['orden'] === 'reciente', fn (Builder $query): Builder => $query->orderByDesc('creado_en'))
            ->when($filters['orden'] === 'nombre_asc', fn (Builder $query): Builder => $query->orderBy('nombre'));
    }

    /**
     * @return array{buscar:?string,estado:?string,orden:string}
     */
    private function filters(Request $request): array
    {
        $search = trim((string) $request->input('buscar', ''));
        $status = trim((string) $request->input('estado', ''));
        $order = trim((string) $request->input('orden', 'nombre_asc'));

        return [
            'buscar' => $search !== '' ? $search : null,
            'estado' => in_array($status, ['activa', 'inactiva'], true) ? $status : null,
            'orden' => in_array($order, ['nombre_asc', 'nombre_desc', 'reciente'], true) ? $order : 'nombre_asc',
        ];
    }

    public function deactivate(Categoria $category, RegistradorAuditoria $audit): RedirectResponse
    {
        $category->update(['activo' => false]);
        $audit->record(request()->user()->id, 'category.deactivated', 'categorias', $category->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'CategorÃ­a desactivada.');
    }
}
