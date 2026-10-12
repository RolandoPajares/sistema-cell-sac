<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * @param  array{presupuesto?:numeric-string|float|int|null, uso?:string|null, prioridad?:string|null}  $filters
     */
    public function recommend(array $filters): Collection
    {
        $weights = match ($filters['uso'] ?? 'general') {
            'gaming' => ['rendimiento' => 1.7, 'bateria' => 1.1, 'camara' => .4, 'valor' => 1.2],
            'camera' => ['rendimiento' => .6, 'bateria' => .7, 'camara' => 1.8, 'valor' => 1.0],
            'study' => ['rendimiento' => 1.0, 'bateria' => 1.4, 'camara' => .5, 'valor' => 1.5],
            'work' => ['rendimiento' => 1.4, 'bateria' => 1.3, 'camara' => .6, 'valor' => 1.1],
            'social' => ['rendimiento' => .8, 'bateria' => 1.0, 'camara' => 1.5, 'valor' => 1.2],
            default => ['rendimiento' => 1.0, 'bateria' => 1.0, 'camara' => 1.0, 'valor' => 1.0],
        };

        if (isset($filters['prioridad'])) {
            $weights[$filters['prioridad']] += .9;
        }

        return Product::with('features', 'intelligenceProfile', 'category', 'images')
            ->active()
            ->get()
            ->filter(fn (Product $product): bool => ! isset($filters['presupuesto']) || (float) $product->effective_price <= (float) $filters['presupuesto'])
            ->map(function (Product $product) use ($weights): Product {
                $features = $product->features->pluck('valor', 'nombre')->map(fn (string $value): float => (float) $value);
                $profile = $product->intelligenceProfile;
                $scores = [
                    'rendimiento' => $profile?->puntuacion_rendimiento ?? $features['rendimiento'] ?? 70,
                    'camara' => $profile?->puntuacion_camara ?? $features['camara'] ?? 70,
                    'bateria' => $profile?->puntuacion_bateria ?? $features['bateria'] ?? 70,
                    'valor' => $profile?->puntuacion_valor ?? $features['valor'] ?? 70,
                ];
                $sum = 0.0;
                $divisor = 0.0;
                foreach ($weights as $key => $weight) {
                    $sum += ($scores[$key] ?? 70) * $weight;
                    $divisor += $weight;
                }
                $product->recommendation_score = (int) round($sum / max($divisor, 1));

                return $product;
            })
            ->sortByDesc('recommendation_score')
            ->take(10)
            ->values();
    }
}
