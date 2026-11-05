<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;

it('returns only active catalog products through the versioned API', function () {
    $category = Categoria::factory()->create(['nombre' => 'Celulares']);
    $activeProduct = Producto::factory()->create([
        'categoria_id' => $category->id,
        'categoria' => $category->nombre,
        'nombre' => 'Galaxy API',
        'activo' => true,
    ]);
    Producto::factory()->create([
        'categoria_id' => $category->id,
        'categoria' => $category->nombre,
        'nombre' => 'Producto oculto API',
        'activo' => false,
    ]);

    $response = $this->getJson(route('api.v1.catalog.index', ['q' => 'Galaxy']));

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeProduct->id)
        ->assertJsonPath('data.0.name', 'Galaxy API')
        ->assertJsonPath('data.0.category.name', 'Celulares');
});

it('returns a not found response for an inactive API catalog product', function () {
    $product = Producto::factory()->create(['activo' => false]);

    $this->getJson(route('api.v1.catalog.show', $product))->assertNotFound();
});

it('rejects unsupported catalog sorting values in the API', function () {
    $this->getJson(route('api.v1.catalog.index', ['orden' => 'random']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['orden']);
});

it('rate limits repeated catalog API requests', function () {
    $url = route('api.v1.catalog.index');

    foreach (range(1, 60) as $attempt) {
        $this->getJson($url)->assertOk();
    }

    $this->getJson($url)->assertTooManyRequests();
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
