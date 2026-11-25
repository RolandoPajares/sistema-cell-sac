<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;

it('muestra categorias primero, marcas despues y productos al final', function (): void {
    $categoria = Categoria::factory()->create(['nombre' => 'Celulares']);
    $producto = Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'categoria' => $categoria->nombre,
        'marca' => 'Samsung',
        'nombre' => 'Galaxy de prueba',
    ]);

    $root = $this->getJson(route('catalog.menu'));
    expect(collect($root->json('opciones'))->pluck('nombre'))
        ->toContain('Celulares')
        ->not->toContain('Samsung');
    expect(collect($root->json('opciones'))->firstWhere('nombre', 'Celulares')['tiene_hijos'])->toBeTrue();

    $category = $this->getJson(route('catalog.menu', ['categoria' => $categoria->id]));
    expect(collect($category->json('opciones'))->pluck('nombre'))
        ->toContain('Samsung')
        ->not->toContain('Galaxy de prueba');
    expect(collect($category->json('opciones'))->firstWhere('nombre', 'Samsung')['url'])
        ->toContain('/catalogo');

    $brand = $this->getJson(route('catalog.menu', [
        'categoria' => $categoria->id,
        'marca' => [$producto->marca],
    ]));
    expect(collect($brand->json('opciones'))->pluck('nombre'))
        ->toContain('Galaxy de prueba')
        ->not->toContain('Samsung');
});
