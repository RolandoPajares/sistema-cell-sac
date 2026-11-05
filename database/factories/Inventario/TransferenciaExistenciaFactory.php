<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\TransferenciaExistencia;
use App\Models\Organizacion\Almacen;
use App\Models\Organizacion\Empresa;
use App\Models\Organizacion\Sede;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransferenciaExistencia>
 */
class TransferenciaExistenciaFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (TransferenciaExistencia $transfer): void {
            $companyId = $transfer->empresa_id;
            if ($companyId === null) {
                $companyId = Empresa::factory()->create()->getKey();
            }
            $sourceSiteId = $transfer->sede_origen_id;
            if ($sourceSiteId === null) {
                $sourceSiteId = Sede::factory()->create(['empresa_id' => $companyId])->getKey();
            }
            $destinationSiteId = $transfer->sede_destino_id;
            if ($destinationSiteId === null) {
                $destinationSiteId = Sede::factory()->create(['empresa_id' => $companyId])->getKey();
            }
            $sourceWarehouseId = $transfer->almacen_origen_id;
            if ($sourceWarehouseId === null) {
                $sourceWarehouseId = Almacen::factory()->create(['sede_id' => $sourceSiteId])->getKey();
            }
            $destinationWarehouseId = $transfer->almacen_destino_id;
            if ($destinationWarehouseId === null) {
                $destinationWarehouseId = Almacen::factory()->create(['sede_id' => $destinationSiteId])->getKey();
            }
            $transfer->forceFill([
                'empresa_id' => $companyId, 'sede_origen_id' => $sourceSiteId, 'almacen_origen_id' => $sourceWarehouseId,
                'sede_destino_id' => $destinationSiteId, 'almacen_destino_id' => $destinationWarehouseId,
            ]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'sede_origen_id' => Sede::factory(),
            'almacen_origen_id' => Almacen::factory(),
            'sede_destino_id' => Sede::factory(),
            'almacen_destino_id' => Almacen::factory(),
            'codigo' => fake()->unique()->bothify('TRF-########'),
            'estado' => 'solicitada',
            'solicitado_por_usuario_id' => User::factory(),
            'creado_en' => now(),
        ];
    }
}
