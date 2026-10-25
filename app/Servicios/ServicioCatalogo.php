<?php

namespace App\Servicios;

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ServicioCatalogo
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(array $filters): Builder
    {
        $productsQuery = Producto::query()->with(['category', 'images'])->active();
        $searchTerm = trim((string) ($filters['q'] ?? ''));

        if ($searchTerm !== '') {
            $productsQuery->where(function (Builder $query) use ($searchTerm): void {
                $query->where('nombre', 'like', '%'.$searchTerm.'%')
                    ->orWhere('marca', 'like', '%'.$searchTerm.'%');
            });
        }

        if (($categoryId = $filters['categoria'] ?? null) !== null && $categoryId !== '') {
            $productsQuery->where('categoria_id', (int) $categoryId);
        }

        if (($brand = trim((string) ($filters['marca'] ?? ''))) !== '') {
            $productsQuery->where('marca', $brand);
        }

        if (($minimumPrice = $filters['min'] ?? null) !== null && $minimumPrice !== '') {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) >= ?', [(float) $minimumPrice]);
        }

        if (($maximumPrice = $filters['max'] ?? null) !== null && $maximumPrice !== '') {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) <= ?', [(float) $maximumPrice]);
        }

        [$field, $direction] = match ($filters['orden'] ?? null) {
            'precio_asc' => ['precio', 'asc'],
            'precio_desc' => ['precio', 'desc'],
            'nuevo' => ['creado_en', 'desc'],
            default => ['nombre', 'asc'],
        };

        return $productsQuery->orderBy($field, $direction)->orderBy('id');
    }

    /**
     * @return array{categories: Collection, brands: Collection}
     */
    public function filterOptions(): array
    {
        return [
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Producto::query()
                ->active()
                ->whereNotNull('marca')
                ->where('marca', '<>', '')
                ->distinct()
                ->orderBy('marca')
                ->pluck('marca'),
        ];
    }
}
