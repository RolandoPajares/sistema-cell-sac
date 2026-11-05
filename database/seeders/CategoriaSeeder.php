<?php

namespace Database\Seeders;

use App\Models\Catalogo\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
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
