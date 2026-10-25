<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Marketing\Campania;
use Illuminate\Support\Collection;

class ServicioInteligenciaComercial
{
    private const MAX_OPTIMIZATION_ITERATIONS = 60;

    private const MAX_VARIETY_ITEMS = 5;

    private const DEFAULT_MARGIN_RATE = 0.15;

    private const MARGIN_GOAL_RATE = 0.20;

    /**
     * Build the same budget proposal offered by the legacy optimizer,
     * using the live catalog instead of arrays assembled in a controller.
     *
     * @return array{items:array<int, array{product:Producto, quantity:int, subtotal:float}, investment:float, balance:float, estimated_margin:float}
     */
    public function optimize(float $budget, string $goal): array
    {
        $products = $this->cellPhones()->sortBy(fn (Producto $product): float => (float) $product->effective_price)->values();

        if ($goal === 'premium') {
            $products = $products->sortByDesc(fn (Producto $product): float => (float) $product->effective_price)->values();
        } elseif ($goal === 'margin') {
            $products = $products->sortByDesc(fn (Producto $product): float => (float) ($product->intelligenceProfile?->puntuacion_valor ?? 70))->values();
        }

        $items = [];
        $investment = 0.0;
        $used = [];
        $iterations = 0;

        while ($iterations++ < self::MAX_OPTIMIZATION_ITERATIONS && $products->isNotEmpty()) {
            $added = false;
            foreach ($products as $product) {
                $price = (float) $product->effective_price;
                $available = max(0, (int) $product->existencias);

                if ($price <= 0 || $available === 0 || $investment + $price > $budget) {
                    continue;
                }
                if ($goal === 'variety' && isset($used[$product->id])) {
                    continue;
                }

                $items[$product->id] ??= ['product' => $product, 'quantity' => 0, 'subtotal' => 0.0];
                if ($items[$product->id]['quantity'] >= $available) {
                    continue;
                }

                $items[$product->id]['quantity']++;
                $items[$product->id]['subtotal'] += $price;
                $used[$product->id] = true;
                $investment += $price;
                $added = true;

                if ($goal === 'variety' && count($used) >= min(self::MAX_VARIETY_ITEMS, $products->count())) {
                    break 2;
                }
                if ($goal === 'premium') {
                    break;
                }
            }

            if (! $added) {
                break;
            }
        }

        $marginRate = $goal === 'margin' ? self::MARGIN_GOAL_RATE : self::DEFAULT_MARGIN_RATE;

        return [
            'items' => array_values($items),
            'investment' => $investment,
            'balance' => max(0, $budget - $investment),
            'estimated_margin' => $investment * $marginRate,
        ];
    }

    /** @return Collection<int, Producto> */
    public function wholesaleProducts(): Collection
    {
        return $this->cellPhones()->take(6)->map(function (Producto $product): Producto {
            $price = (float) $product->effective_price;
            $product->wholesale_price_10 = round($price * .95, 2);
            $product->wholesale_price_50 = round($price * .90, 2);

            return $product;
        });
    }

    /** @return array{active:int, views:int, clicks:int, ctr:float, campaigns:Collection} */
    public function advertisingSummary(): array
    {
        $campaigns = Campania::query()->latest('creado_en')->get();
        $views = (int) $campaigns->sum('vistas');
        $clicks = (int) $campaigns->sum('clics');

        return [
            'active' => $campaigns->where('activo', true)->count(),
            'views' => $views,
            'clicks' => $clicks,
            'ctr' => $views > 0 ? round(($clicks / $views) * 100, 1) : 0.0,
            'campaigns' => $campaigns,
        ];
    }

    /** @return Collection<int, Producto> */
    private function cellPhones(): Collection
    {
        return Producto::query()
            ->with(['category', 'features', 'intelligenceProfile', 'images'])
            ->active()
            ->where(function ($query): void {
                $query->where('categoria', 'like', '%cel%')
                    ->orWhereHas('category', fn ($category): mixed => $category->where('nombre', 'like', '%cel%'))
                    ->orWhere('nombre', 'like', '%iphone%')
                    ->orWhere('nombre', 'like', '%galaxy%');
            })
            ->orderBy('nombre')
            ->get();
    }
}
