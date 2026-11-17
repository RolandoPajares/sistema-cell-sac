<?php

namespace App\Servicios\Inteligente\Comprador;

use App\Models\Catalogo\Producto;
use Illuminate\Support\Collection;

/**
 * Define la responsabilidad de la clase ServicioRecomendaciones dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioRecomendaciones
{
    /**
     * @param  array{presupuesto?:numeric-string|float|int|null, uso?:string|null, prioridad?:string|null}  $filters
     */
    public function recomendar(array $filters): Collection
    {
        // ORM: consulta productos y sus relaciones para calcular recomendaciones.
        // Busca productos y los ordena segun los criterios seleccionados por el comprador.
        // Guarda en $weights el resultado necesario para continuar la operacion.
        $weights = match ($filters['uso'] ?? 'general') {
            'gaming' => ['rendimiento' => 1.7, 'bateria' => 1.1, 'camara' => .4, 'valor' => 1.2],
            'camera' => ['rendimiento' => .6, 'bateria' => .7, 'camara' => 1.8, 'valor' => 1.0],
            'study' => ['rendimiento' => 1.0, 'bateria' => 1.4, 'camara' => .5, 'valor' => 1.5],
            'work' => ['rendimiento' => 1.4, 'bateria' => 1.3, 'camara' => .6, 'valor' => 1.1],
            'social' => ['rendimiento' => .8, 'bateria' => 1.0, 'camara' => 1.5, 'valor' => 1.2],
            default => ['rendimiento' => 1.0, 'bateria' => 1.0, 'camara' => 1.0, 'valor' => 1.0],
        };

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        // Aplica una prioridad opcional sin modificar los filtros originales.
        if (isset($filters['prioridad'])) {
            $weights[$filters['prioridad']] += .9;
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return Producto::with('features', 'perfilInteligencia', 'category', 'images')
            ->active()
            ->get()
            ->filter(fn (Producto $product): bool => ! isset($filters['presupuesto']) || (float) $product->effective_price <= (float) $filters['presupuesto'])
            ->map(function (Producto $product) use ($weights): Producto {
                $features = $product->features->pluck('valor', 'nombre')->map(fn (string $value): float => (float) $value);
                // Guarda en $profile el resultado necesario para continuar la operacion.
                $profile = $product->perfilInteligencia;
                // Agrupa en $scores los datos que se utilizaran en el siguiente paso.
                $scores = [
                    'rendimiento' => $profile?->puntuacion_rendimiento ?? $features['rendimiento'] ?? 70,
                    'camara' => $profile?->puntuacion_camara ?? $features['camara'] ?? 70,
                    'bateria' => $profile?->puntuacion_bateria ?? $features['bateria'] ?? 70,
                    'valor' => $profile?->puntuacion_valor ?? $features['valor'] ?? 70,
                ];
                // Guarda en $sum el resultado necesario para continuar la operacion.
                $sum = 0.0;
                // Guarda en $divisor el resultado necesario para continuar la operacion.
                $divisor = 0.0;
                // Recorre los elementos recibidos para validar o procesar cada registro.
                // Suma el peso de cada coincidencia para obtener el puntaje final.
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
