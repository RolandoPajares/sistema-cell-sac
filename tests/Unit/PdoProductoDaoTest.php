<?php

use App\Dominio\Catalogo\ProductoEntidad;
use App\Dominio\Catalogo\VarianteProductoEntidad;
use App\Infraestructura\Persistencia\Pdo\PdoConexion;
use App\Infraestructura\Persistencia\Pdo\PdoProductoDao;

it('ejecuta CRUD, mapea variantes y protege valores con parámetros enlazados', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE productos (id INTEGER PRIMARY KEY AUTOINCREMENT, marca TEXT NOT NULL, nombre TEXT NOT NULL, categoria_id INTEGER NOT NULL, categoria TEXT NOT NULL, precio NUMERIC NOT NULL, precio_oferta NUMERIC NULL, activo INTEGER NOT NULL, empresa_id INTEGER NULL)');
    $pdo->exec('CREATE TABLE variantes_productos (id INTEGER PRIMARY KEY AUTOINCREMENT, producto_id INTEGER NOT NULL, codigo_sku TEXT NULL, codigo_barras TEXT NULL, nombre_color TEXT NOT NULL, codigo_color TEXT NULL, existencias INTEGER NOT NULL, controla_serie INTEGER NOT NULL, estado TEXT NOT NULL)');
    $dao = new PdoProductoDao(new PdoConexion($pdo));

    $producto = $dao->crear(new ProductoEntidad(null, "Marca'; DROP TABLE productos;--", 'Teléfono', 1, 'Móviles', '100.00', null, true, 7));
    $variante = $dao->crearVariante(new VarianteProductoEntidad(null, (int) $producto->id, 'SKU-1', null, 'Negro', null, 4, false, 'activo'));
    $mapeado = $dao->buscarPorId((int) $producto->id);
    $actualizado = $dao->actualizar(new ProductoEntidad($producto->id, 'Marca', 'Teléfono Pro', 1, 'Móviles', '120.00', '110.00', true, 7));
    $dao->actualizarVariante(new VarianteProductoEntidad($variante->id, (int) $producto->id, 'SKU-2', null, 'Azul', null, 3, false, 'activo'));

    expect($mapeado?->marca)->toBe("Marca'; DROP TABLE productos;--")
        ->and($mapeado?->variantes)->toHaveCount(1)
        ->and($actualizado->nombre)->toBe('Teléfono Pro')
        ->and($dao->buscarPorId((int) $producto->id)?->marca)->toBe('Marca');

    $dao->eliminarVariante((int) $variante->id);
    $dao->eliminar((int) $producto->id);
    expect($dao->buscarPorId((int) $producto->id))->toBeNull();
});

it('rechaza datos inválidos antes de abrir una transacción de escritura', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE productos (id INTEGER PRIMARY KEY AUTOINCREMENT, marca TEXT NOT NULL, nombre TEXT NOT NULL, categoria_id INTEGER NOT NULL, categoria TEXT NOT NULL, precio NUMERIC NOT NULL, precio_oferta NUMERIC NULL, activo INTEGER NOT NULL, empresa_id INTEGER NULL)');
    $dao = new PdoProductoDao(new PdoConexion($pdo));

    expect(fn (): ProductoEntidad => $dao->crear(new ProductoEntidad(null, '', 'Inválido', 1, 'Móviles', '1.00', null, true, null)))
        ->toThrow(DomainException::class);
    expect((int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn())->toBe(0);
});
