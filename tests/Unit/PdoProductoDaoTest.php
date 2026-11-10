<?php

use App\Dominio\Catalogo\ProductoDao;
use App\Dominio\Catalogo\ProductoEntidad;
use App\Dominio\Catalogo\VarianteProductoEntidad;
use App\Infraestructura\Persistencia\Pdo\PdoConexion;
use App\Infraestructura\Persistencia\Pdo\PdoHidratador;
use App\Infraestructura\Persistencia\Pdo\PdoMapaEntidad;
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
    $pdo->exec('CREATE TABLE variantes_productos (id INTEGER PRIMARY KEY AUTOINCREMENT, producto_id INTEGER NOT NULL, codigo_sku TEXT NULL, codigo_barras TEXT NULL, nombre_color TEXT NOT NULL, codigo_color TEXT NULL, existencias INTEGER NOT NULL, controla_serie INTEGER NOT NULL, estado TEXT NOT NULL)');
    $dao = new PdoProductoDao(new PdoConexion($pdo));

    expect(fn (): ProductoEntidad => $dao->crear(new ProductoEntidad(null, '', 'Inválido', 1, 'Móviles', '1.00', null, true, null)))
        ->toThrow(DomainException::class);
    expect((int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn())->toBe(0);
});

it('revierte el producto y la variante cuando falla el caso de negocio coordinado', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE productos (id INTEGER PRIMARY KEY AUTOINCREMENT, marca TEXT NOT NULL, nombre TEXT NOT NULL, categoria_id INTEGER NOT NULL, categoria TEXT NOT NULL, precio NUMERIC NOT NULL, precio_oferta NUMERIC NULL, activo INTEGER NOT NULL, empresa_id INTEGER NULL)');
    $pdo->exec('CREATE TABLE variantes_productos (id INTEGER PRIMARY KEY AUTOINCREMENT, producto_id INTEGER NOT NULL, codigo_sku TEXT UNIQUE NOT NULL, codigo_barras TEXT NULL, nombre_color TEXT NOT NULL, codigo_color TEXT NULL, existencias INTEGER NOT NULL, controla_serie INTEGER NOT NULL, estado TEXT NOT NULL)');
    $dao = new PdoProductoDao(new PdoConexion($pdo));

    expect(fn (): mixed => $dao->enTransaccion(function (ProductoDao $transactionalDao): void {
        $producto = $transactionalDao->crear(new ProductoEntidad(null, 'Marca', 'Producto rollback', 1, 'Móviles', '10.00', null, true, 7));
        $transactionalDao->crearVariante(new VarianteProductoEntidad(null, (int) $producto->id, 'SKU-ROLLBACK', null, 'Negro', null, 1, false, 'activo'));
        throw new RuntimeException('Fallo simulado del caso de negocio.');
    }))->toThrow(RuntimeException::class, 'Fallo simulado del caso de negocio.');

    expect((int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn())->toBe(0)
        ->and((int) $pdo->query('SELECT COUNT(*) FROM variantes_productos')->fetchColumn())->toBe(0);
});

it('filtra y pagina productos sin interpolar valores ni identificadores SQL', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE productos (id INTEGER PRIMARY KEY AUTOINCREMENT, marca TEXT NOT NULL, nombre TEXT NOT NULL, categoria_id INTEGER NOT NULL, categoria TEXT NOT NULL, precio NUMERIC NOT NULL, precio_oferta NUMERIC NULL, activo INTEGER NOT NULL, empresa_id INTEGER NULL)');
    $pdo->exec('CREATE TABLE variantes_productos (id INTEGER PRIMARY KEY AUTOINCREMENT, producto_id INTEGER NOT NULL, codigo_sku TEXT NULL, codigo_barras TEXT NULL, nombre_color TEXT NOT NULL, codigo_color TEXT NULL, existencias INTEGER NOT NULL, controla_serie INTEGER NOT NULL, estado TEXT NOT NULL)');
    $dao = new PdoProductoDao(new PdoConexion($pdo));

    foreach ([
        ['Marca A', 'Producto uno', 1, true, 7],
        ['Marca B', 'Producto dos', 1, true, 7],
        ['Marca A', 'Producto tres', 2, false, 8],
    ] as [$marca, $nombre, $categoriaId, $activo, $empresaId]) {
        $dao->crear(new ProductoEntidad(null, $marca, $nombre, $categoriaId, 'Móviles', '10.00', null, $activo, $empresaId));
    }

    $resultado = $dao->listar([
        'buscar' => "Marca A' OR 1=1 --",
        'activo' => true,
        'empresa_id' => 7,
    ], 1, 1);

    expect($resultado->total)->toBe(0)
        ->and($resultado->items)->toHaveCount(0)
        ->and($resultado->ultimaPagina())->toBe(1);

    $resultado = $dao->listar(['buscar' => 'Producto', 'activo' => true, 'empresa_id' => 7], 2, 1);

    expect($resultado->total)->toBe(2)
        ->and($resultado->items)->toHaveCount(1)
        ->and($resultado->pagina)->toBe(2)
        ->and($resultado->ultimaPagina())->toBe(2)
        ->and($resultado->items[0]->nombre)->toBe('Producto uno');
});

it('conserva una transaccion externa bajo el control de quien la inicio', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion = new PdoConexion($pdo);
    $pdo->beginTransaction();

    $resultado = $conexion->ejecutarEnTransaccion(function (PDO $conexion): string {
        $conexion->exec('CREATE TABLE prueba_transaccion (id INTEGER PRIMARY KEY AUTOINCREMENT, valor TEXT NOT NULL)');
        $conexion->exec("INSERT INTO prueba_transaccion (valor) VALUES ('pendiente')");

        return 'ok';
    });

    expect($resultado)->toBe('ok')
        ->and($pdo->inTransaction())->toBeTrue();

    $pdo->rollBack();
});

it('reutiliza el mapeador para tipos nulos y decimales sin aceptar identificadores arbitrarios', function (): void {
    $mapa = new PdoMapaEntidad('variantes_productos', 'id', [
        'id' => 'id', 'codigoSku' => 'codigo_sku', 'existencias' => 'existencias', 'controlaSerie' => 'controla_serie', 'precio' => 'precio',
    ], [
        'id' => 'int', 'codigoSku' => 'string', 'existencias' => 'int', 'controlaSerie' => 'bool', 'precio' => 'decimal',
    ]);
    $hidratador = new PdoHidratador;

    $atributos = $hidratador->atributosDesdeFila([
        'id' => '8', 'codigo_sku' => null, 'existencias' => '3', 'controla_serie' => 1, 'precio' => '10.90',
    ], $mapa);

    expect($atributos)->toBe([
        'id' => 8, 'codigoSku' => null, 'existencias' => 3, 'controlaSerie' => true, 'precio' => '10.90',
    ])
        ->and(fn (): PdoMapaEntidad => new PdoMapaEntidad('productos;DROP', 'id', ['id' => 'id'], ['id' => 'int']))
        ->toThrow(InvalidArgumentException::class);
});

it('limita el DAO acotado a su empresa y protege variantes de otra empresa', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE productos (id INTEGER PRIMARY KEY AUTOINCREMENT, marca TEXT NOT NULL, nombre TEXT NOT NULL, categoria_id INTEGER NOT NULL, categoria TEXT NOT NULL, precio NUMERIC NOT NULL, precio_oferta NUMERIC NULL, activo INTEGER NOT NULL, empresa_id INTEGER NULL)');
    $pdo->exec('CREATE TABLE variantes_productos (id INTEGER PRIMARY KEY AUTOINCREMENT, producto_id INTEGER NOT NULL, codigo_sku TEXT NULL, codigo_barras TEXT NULL, nombre_color TEXT NOT NULL, codigo_color TEXT NULL, existencias INTEGER NOT NULL, controla_serie INTEGER NOT NULL, estado TEXT NOT NULL)');
    $dao = new PdoProductoDao(new PdoConexion($pdo));
    $empresaA = $dao->crear(new ProductoEntidad(null, 'A', 'Producto A', 1, 'Móviles', '10.00', null, true, 7));
    $empresaB = $dao->crear(new ProductoEntidad(null, 'B', 'Producto B', 1, 'Móviles', '20.00', null, true, 8));
    $varianteB = $dao->crearVariante(new VarianteProductoEntidad(null, (int) $empresaB->id, 'SKU-B', null, 'Negro', null, 1, false, 'activo'));
    $acotado = $dao->conEmpresa(7);

    expect($acotado->buscarPorId((int) $empresaA->id))->not->toBeNull()
        ->and($acotado->buscarPorId((int) $empresaB->id))->toBeNull()
        ->and(fn (): ProductoEntidad => $acotado->actualizar(new ProductoEntidad((int) $empresaB->id, 'X', 'Intrusión', 1, 'Móviles', '1.00', null, true, 8)))
        ->toThrow(DomainException::class)
        ->and(fn (): mixed => $acotado->actualizarVariante(new VarianteProductoEntidad((int) $varianteB->id, (int) $empresaB->id, 'SKU-X', null, 'Rojo', null, 1, false, 'activo')))
        ->toThrow(DomainException::class);

    $acotado->eliminar((int) $empresaB->id);
    $acotado->eliminarVariante((int) $varianteB->id);

    expect($dao->buscarPorId((int) $empresaB->id))->not->toBeNull()
        ->and($dao->buscarPorId((int) $empresaB->id)?->variantes)->toHaveCount(1);
});

it('does not leak company scope between immutable DAO instances', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE productos (id INTEGER PRIMARY KEY AUTOINCREMENT, marca TEXT NOT NULL, nombre TEXT NOT NULL, categoria_id INTEGER NOT NULL, categoria TEXT NOT NULL, precio NUMERIC NOT NULL, precio_oferta NUMERIC NULL, activo INTEGER NOT NULL, empresa_id INTEGER NULL)');
    $pdo->exec('CREATE TABLE variantes_productos (id INTEGER PRIMARY KEY AUTOINCREMENT, producto_id INTEGER NOT NULL, codigo_sku TEXT NULL, codigo_barras TEXT NULL, nombre_color TEXT NOT NULL, codigo_color TEXT NULL, existencias INTEGER NOT NULL, controla_serie INTEGER NOT NULL, estado TEXT NOT NULL)');
    $dao = new PdoProductoDao(new PdoConexion($pdo));
    $productoA = $dao->crear(new ProductoEntidad(null, 'A', 'Producto A', 1, 'Móviles', '10.00', null, true, 7));
    $productoB = $dao->crear(new ProductoEntidad(null, 'B', 'Producto B', 1, 'Móviles', '20.00', null, true, 8));

    $empresaA = $dao->conEmpresa(7);
    $empresaB = $dao->conEmpresa(8);
    $empresaADeNuevo = $dao->conEmpresa(7);

    expect($empresaA->buscarPorId((int) $productoA->id))->not->toBeNull()
        ->and($empresaA->buscarPorId((int) $productoB->id))->toBeNull()
        ->and($empresaB->buscarPorId((int) $productoB->id))->not->toBeNull()
        ->and($empresaADeNuevo->buscarPorId((int) $productoA->id))->not->toBeNull()
        ->and($empresaADeNuevo->buscarPorId((int) $productoB->id))->toBeNull()
        ->and($dao->buscarPorId((int) $productoA->id))->not->toBeNull()
        ->and($dao->buscarPorId((int) $productoB->id))->not->toBeNull()
        ->and(fn (): ProductoDao => $dao->conEmpresa(0))
        ->toThrow(DomainException::class);
});
