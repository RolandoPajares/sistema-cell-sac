<?php

namespace App\Servicios\Inteligente;

use App\Models\Catalogo\Producto;
use App\Models\Marketing\Campania;
use Illuminate\Support\Collection;

/**
 * Define la responsabilidad de la clase ServicioInteligenciaComercial dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
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
    public function optimizar(float $budget, string $goal): array
    {
        $products = $this->celulares()->sortBy(fn(Producto $product): float => (float) $product->effective_price)->values();

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($goal === 'premium') {
            $products = $products->sortByDesc(fn(Producto $product): float => (float) $product->effective_price)->values();
        } elseif ($goal === 'margin') {
            $products = $products->sortByDesc(fn(Producto $product): float => (float) ($product->perfilInteligencia?->puntuacion_valor ?? 70))->values();
        }

        // Agrupa en $items los datos que se utilizaran en el siguiente paso.
        $items = [];
        // Guarda en $investment el resultado necesario para continuar la operacion.
        $investment = 0.0;
        // Agrupa en $used los datos que se utilizaran en el siguiente paso.
        $used = [];
        // Guarda en $iterations el resultado necesario para continuar la operacion.
        $iterations = 0;

        while ($iterations++ < self::MAX_OPTIMIZATION_ITERATIONS && $products->isNotEmpty()) {
            // Guarda en $added el resultado necesario para continuar la operacion.
            $added = false;
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($products as $product) {
                // Guarda en $price el resultado necesario para continuar la operacion.
                $price = (float) $product->effective_price;
                // Guarda en $available el resultado necesario para continuar la operacion.
                $available = max(0, (int) $product->existencias);

                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($price <= 0 || $available === 0 || $investment + $price > $budget) {
                    continue;
                }
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($goal === 'variety' && isset($used[$product->id])) {
                    continue;
                }

                $items[$product->id] ??= ['product' => $product, 'quantity' => 0, 'subtotal' => 0.0];
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($items[$product->id]['quantity'] >= $available) {
                    continue;
                }

                $items[$product->id]['quantity']++;
                $items[$product->id]['subtotal'] += $price;
                $used[$product->id] = true;
                $investment += $price;
                // Guarda en $added el resultado necesario para continuar la operacion.
                $added = true;

                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($goal === 'variety' && count($used) >= min(self::MAX_VARIETY_ITEMS, $products->count())) {
                    break 2;
                }
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($goal === 'premium') {
                    break;
                }
            }

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
    public function productosMayoristas(): Collection
    {
        return $this->celulares()->take(6)->map(function (Producto $product): Producto {
            // Guarda en $price el resultado necesario para continuar la operacion.
            $price = (float) $product->effective_price;
            $product->wholesale_price_10 = round($price * .95, 2);
            $product->wholesale_price_50 = round($price * .90, 2);
            $product->wholesale_image = $product->url_imagen_principal ?: asset('assets/cell-legacy/img/marca/logo.jpeg');

            return $product;
        });
    }

    /** @return array{active:int, views:int, clicks:int, ctr:float, campaigns:Collection} */
    public function resumenPublicidad(): array
    {
        // Prepara en $campaigns la consulta que obtiene o modifica los datos necesarios.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        $campaigns = Campania::query()->latest('creado_en')->get();
        // Guarda en $views el resultado necesario para continuar la operacion.
        $views = (int) $campaigns->sum('vistas');
        // Guarda en $clicks el resultado necesario para continuar la operacion.
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
    private function celulares(): Collection
    {
        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return Producto::query()
            ->with(['category', 'features', 'perfilInteligencia', 'images'])
            ->active()
            ->where(function ($query): void {
                $query->where('categoria', 'like', '%cel%')
                    ->orWhereHas('category', fn($category): mixed => $category->where('nombre', 'like', '%cel%'))
                    ->orWhere('nombre', 'like', '%iphone%')
                    ->orWhere('nombre', 'like', '%galaxy%');
            })
            ->orderBy('nombre')
            ->get();
    }
}
