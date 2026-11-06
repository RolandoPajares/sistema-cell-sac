<?php

namespace Database\Seeders;

use App\Models\Catalogo\Categoria;
use Illuminate\Database\Seeder;

/**
 * Define la responsabilidad de la clase CategoriaSeeder dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CategoriaSeeder extends Seeder
{
    /**
     * Ejecuta la operación principal asignada al componente.
     *
     * @return void Resultado de la operación.
     */
    public function run(): void
    {
        foreach ([
            ['nombre' => 'Celulares', 'descripcion' => 'Smartphones y equipos móviles'],
            ['nombre' => 'Audio', 'descripcion' => 'Audífonos, parlantes y accesorios de audio'],
            ['nombre' => 'Accesorios', 'descripcion' => 'Cargadores, cables, fundas y complementos'],
        ] as $category) {
            Categoria::firstOrCreate(
                ['nombre' => $category['nombre']],
                $category + ['activo' => true, 'creado_en' => now()],
            );
        }
    }
}
