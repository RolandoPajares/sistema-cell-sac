<?php

use App\Models\Catalogo\Atributo;
use App\Models\Catalogo\AtributoValor;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use Illuminate\Support\Facades\Schema;

it('renderiza el catalogo publico con filtros facetados sin cambiar su ruta', function (): void {
    $respuesta = $this->get(route('catalog.index'));

    $respuesta->assertOk()
        ->assertSee('catalog-sidebar')
        ->assertSee('catalog-search-form');
});

it('acepta filtros dinamicos solo cuando la estructura EAV existe', function (): void {
    expect(Schema::hasTable('atributo_valores'))->toBeTrue();

    $categoria = Categoria::factory()->create();
    $productoFiltrado = Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'categoria' => $categoria->nombre,
        'nombre' => 'Producto con memoria filtrable',
    ]);
    Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'categoria' => $categoria->nombre,
        'nombre' => 'Producto sin memoria filtrable',
    ]);
    $otraCategoria = Categoria::factory()->create();
    $productoFueraDeCategoria = Producto::factory()->create([
        'categoria_id' => $otraCategoria->id,
        'categoria' => $otraCategoria->nombre,
        'nombre' => 'Producto de otra categoria',
    ]);
    $atributo = Atributo::create(['nombre' => 'Memoria', 'tipo_dato' => 'texto']);
    $valor = AtributoValor::create([
        'atributo_id' => $atributo->id,
        'valor' => '256 GB',
        'valor_normalizado' => '256 gb',
    ]);
    AtributoValor::create([
        'atributo_id' => $atributo->id,
        'valor' => '512 GB',
        'valor_normalizado' => '512 gb',
    ]);
    $productoFiltrado->attributeValues()->attach($valor->id);
    $valorFueraDeCategoria = AtributoValor::query()->where('valor_normalizado', '512 gb')->firstOrFail();
    $productoFueraDeCategoria->attributeValues()->attach($valorFueraDeCategoria->id);

    $respuesta = $this->get(route('catalog.index', [
        'categoria' => $categoria->id,
        'atributos' => [$atributo->id => [$valor->id]],
    ]));

    $respuesta->assertOk()
        ->assertSee('Producto con memoria filtrable')
        ->assertDontSee('Producto sin memoria filtrable')
        ->assertSee('256 GB')
        ->assertDontSee('512 GB');
});

it('expone el menu lateral con categorias, marcas y atributos compatibles', function (): void {
    $categoria = Categoria::factory()->create(['nombre' => 'Celulares menú']);
    $producto = Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'categoria' => $categoria->nombre,
        'marca' => 'Marca menú',
        'nombre' => 'Producto menú',
    ]);
    $atributo = Atributo::create(['nombre' => 'Memoria menú', 'tipo_dato' => 'texto']);
    $valor = AtributoValor::create([
        'atributo_id' => $atributo->id,
        'valor' => '8 GB',
        'valor_normalizado' => '8 gb',
    ]);
    $producto->attributeValues()->attach($valor->id);

    $respuesta = $this->getJson(route('catalog.menu'));

    $respuesta->assertOk()->assertJsonPath('total_productos', 1);
    expect(collect($respuesta->json('opciones'))->pluck('nombre'))
        ->toContain('Celulares menú')
        ->not->toContain('Ver productos compatibles', 'DEMO Operaciones Comerciales');
});
