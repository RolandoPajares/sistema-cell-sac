<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Product;
use Illuminate\Support\Collection;

class CommerceIntelligenceService
{
    /**
     * Build the same budget proposal offered by the legacy optimizer,
     * using the live catalog instead of arrays assembled in a controller.
     *
     * @return array{items:array<int, array{product:Product, quantity:int, subtotal:float}, investment:float, balance:float, estimated_margin:float}
     */
    public function optimize(float $budget, string $goal): array
    {
        $products = $this->cellPhones()->sortBy(fn (Product $product): float => (float) $product->effective_price)->values();

        if ($goal === 'premium') {
            $products = $products->sortByDesc(fn (Product $product): float => (float) $product->effective_price)->values();
        } elseif ($goal === 'margin') {
            $products = $products->sortByDesc(fn (Product $product): float => (float) ($product->intelligenceProfile?->puntuacion_valor ?? 70))->values();
        }

        $items = [];
        $investment = 0.0;
        $used = [];
        $iterations = 0;

        while ($iterations++ < 60 && $products->isNotEmpty()) {
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

                if ($goal === 'variety' && count($used) >= min(5, $products->count())) {
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

        $marginRate = $goal === 'margin' ? 0.20 : 0.15;

        return [
            'items' => array_values($items),
            'investment' => $investment,
            'balance' => max(0, $budget - $investment),
            'estimated_margin' => $investment * $marginRate,
        ];
    }

    /** @return Collection<int, Product> */
    public function wholesaleProducts(): Collection
    {
        return $this->cellPhones()->take(6)->map(function (Product $product): Product {
            $price = (float) $product->effective_price;
            $product->wholesale_price_10 = round($price * .95, 2);
            $product->wholesale_price_50 = round($price * .90, 2);

            return $product;
        });
    }

    /** @return array{active:int, views:int, clicks:int, ctr:float, campaigns:Collection} */
    public function advertisingSummary(): array
    {
        $campaigns = Campaign::query()->latest('creado_en')->get();
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

    /** @return Collection<int, Product> */
    private function cellPhones(): Collection
    {
        return Product::query()
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
