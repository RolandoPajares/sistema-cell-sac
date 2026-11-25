<?php

namespace App\Servicios\Compartidos\Catalogo;

use App\Models\Catalogo\Categoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ServicioNavegacionCatalogo
{
    public function __construct(private readonly ServicioCatalogo $catalogo) {}

    /** @param array<string, mixed> $filters */
    public function menu(array $filters): array
    {
        $products = $this->catalogo->query($filters);
        $total = (clone $products)->reorder()->count('productos.id');
        $categoryId = $this->nullableInt($filters['categoria'] ?? null);
        $attributeId = $this->nullableInt($filters['atributo'] ?? null);
        $brandSelected = $categoryId !== null && $this->values($filters['marca'] ?? null) !== [];
        $options = $categoryId !== null && $this->hasChildren($categoryId)
            ? $this->categoryOptions($categoryId, $filters)
            : ($attributeId !== null
                ? $this->valueOptions($attributeId, $filters, $products)
                : ($brandSelected ? $this->productOptions($products) : $this->facetOptions($filters, $products)));

        return [
            'titulo' => $categoryId === null ? 'Explorar catálogo' : (string) Categoria::query()->whereKey($categoryId)->value('nombre'),
            'tipo_menu' => $attributeId !== null ? 'valor_atributo' : ($categoryId === null ? 'categoria' : 'facetas'),
            'nivel' => $categoryId === null ? 0 : 1,
            'contexto' => [
                'categoria_id' => $categoryId,
                'atributo_id' => $attributeId,
                'marcas' => $this->values($filters['marca'] ?? null),
                'atributos' => $filters['atributos'] ?? [],
            ],
            'opciones' => array_values($options),
            'total_productos' => $total,
        ];
    }

    /** @param array<string, mixed> $filters */
    private function categoryOptions(int $categoryId, array $filters): array
    {
        $query = Categoria::query()->where('activo', true)->where('categoria_padre_id', $categoryId);

        if (Schema::hasColumn('categorias', 'orden')) {
            $query->orderBy('orden');
        }

        return $query->orderBy('nombre')->get()->map(function (Categoria $category) use ($filters): array {
            $next = $filters;
            $next['categoria'] = $category->id;
            $count = $this->catalogo->query($next)->reorder()->count('productos.id');

            return $this->option($category->id, 'categoria', $category->nombre, $count, $next, $count > 0);
        })->all();
    }

    /** @param array<string, mixed> $filters */
    private function facetOptions(array $filters, Builder $products): array
    {
        $options = [];
        $categoryId = $this->nullableInt($filters['categoria'] ?? null);
        if ($categoryId === null) {
            $categories = Categoria::query()->where('activo', true);
            if (Schema::hasColumn('categorias', 'categoria_padre_id')) {
                $categories->whereNull('categoria_padre_id');
            }
            $categories->whereRaw('LOWER(nombre) <> ?', ['demo operaciones comerciales']);
            if (Schema::hasColumn('categorias', 'orden')) {
                $categories->orderBy('orden');
            }
            foreach ($categories->orderBy('nombre')->get() as $category) {
                $next = $filters;
                $next['categoria'] = $category->id;
                $count = $this->catalogo->query($next)->reorder()->count('productos.id');
                if ($count > 0) {
                    $options[] = $this->option($category->id, 'categoria', $category->nombre, $count, $next, true);
                }
            }

            return $options;
        }

        $brands = (clone $products)->reorder()->whereNotNull('marca')->where('marca', '<>', '')
            ->select('marca')->selectRaw('COUNT(DISTINCT productos.id) AS total')->groupBy('marca')->orderBy('marca')->get();
        foreach ($brands as $brand) {
            $next = $filters;
            $next['marca'] = array_values(array_unique(array_merge($this->values($filters['marca'] ?? null), [(string) $brand->marca])));
            $option = $this->option((string) $brand->marca, 'marca', (string) $brand->marca, (int) $brand->total, $next, true);
            $option['url'] = route('catalog.index', $this->queryParams($next));
            $options[] = $option;
        }

        return $options;
    }

    /** @return array<int, array<string, mixed>> */
    private function productOptions(Builder $products): array
    {
        return (clone $products)->reorder()->select(['productos.id', 'productos.nombre', 'productos.marca'])
            ->orderBy('productos.nombre')->limit(100)->get()->map(fn (object $product): array => [
                'id' => (int) $product->id,
                'tipo' => 'producto',
                'nombre' => (string) $product->nombre,
                'marca' => (string) $product->marca,
                'tiene_hijos' => false,
                'cantidad_productos' => 1,
                'url' => route('catalog.show', $product->id),
            ])->all();
    }

    /** @param array<string, mixed> $filters */
    private function valueOptions(int $attributeId, array $filters, Builder $products): array
    {
        return (clone $products)->reorder()->join('producto_atributo_valor as pivote', 'pivote.producto_id', '=', 'productos.id')
            ->join('atributo_valores as valor', 'valor.id', '=', 'pivote.atributo_valor_id')->where('valor.atributo_id', $attributeId)
            ->where('valor.activo', true)->select('valor.id', 'valor.valor')->selectRaw('COUNT(DISTINCT productos.id) AS total')
            ->groupBy('valor.id', 'valor.valor')->orderBy('valor.orden')->orderBy('valor.valor')->get()->map(fn (object $value): array => [
                'id' => (int) $value->id,
                'tipo' => 'valor_atributo',
                'nombre' => (string) $value->valor,
                'tiene_hijos' => false,
                'cantidad_productos' => (int) $value->total,
            ])->map(function (array $option) use ($attributeId, $filters): array {
                $next = $filters;
                $next['atributos'][$attributeId] = [$option['id']];
                unset($next['atributo']);
                $option['parametros'] = $this->queryParams($next);

                return $option;
            })->all();
    }

    /** @param array<string, mixed> $filters */
    private function option(int|string $id, string $type, string $name, int $count, array $filters, bool $children): array
    {
        return ['id' => $id, 'tipo' => $type, 'nombre' => $name, 'tiene_hijos' => $children, 'cantidad_productos' => $count, 'parametros' => $this->queryParams($filters)];
    }

    private function hasChildren(int $categoryId): bool
    {
        return Schema::hasColumn('categorias', 'categoria_padre_id') && Categoria::query()->where('activo', true)->where('categoria_padre_id', $categoryId)->exists();
    }

    /** @param array<string, mixed> $filters */
    private function queryParams(array $filters): array
    {
        return collect($filters)->except('atributo')->filter(fn (mixed $value): bool => $value !== null && $value !== '' && $value !== [])->all();
    }

    /** @return array<int, string> */
    private function values(mixed $value): array
    {
        return collect(is_array($value) ? $value : [$value])->map(fn (mixed $item): string => trim((string) $item))->filter()->unique()->take(20)->values()->all();
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
