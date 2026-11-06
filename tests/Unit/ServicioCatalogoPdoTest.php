<?php

use App\Dominio\Catalogo\ProductoEntidad;
use App\Infraestructura\Persistencia\Pdo\PdoConexion;
use App\Infraestructura\Persistencia\Pdo\PdoProductoDao;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCatalogo;
use App\Servicios\ServicioInventario;
use App\Servicios\RegistradorAuditoria;

it('usa el DAO PDO desde un servicio de negocio y confirma producto con variante atómicamente', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE productos (id INTEGER PRIMARY KEY AUTOINCREMENT, marca TEXT NOT NULL, nombre TEXT NOT NULL, categoria_id INTEGER NOT NULL, categoria TEXT NOT NULL, precio NUMERIC NOT NULL, precio_oferta NUMERIC NULL, activo INTEGER NOT NULL, empresa_id INTEGER NULL)');
    $pdo->exec('CREATE TABLE variantes_productos (id INTEGER PRIMARY KEY AUTOINCREMENT, producto_id INTEGER NOT NULL, codigo_sku TEXT NOT NULL, codigo_barras TEXT NULL, nombre_color TEXT NOT NULL, codigo_color TEXT NULL, existencias INTEGER NOT NULL, controla_serie INTEGER NOT NULL, estado TEXT NOT NULL)');

    $catalogo = new ServicioCatalogo(
        new ServicioInventario(new ServicioAutorizacion(), new RegistradorAuditoria()),
        new ServicioAutorizacion(),
        new PdoProductoDao(new PdoConexion($pdo)),
    );

    $producto = $catalogo->crearProductoConVariante(
        new ProductoEntidad(null, 'Marca', 'Producto servicio', 1, 'Móviles', '25.00', null, true, 7),
        'SKU-SERVICIO',
        'Negro',
        4,
    );

    expect($producto->id)->toBeGreaterThan(0)
        ->and($producto->variantes)->toHaveCount(1)
        ->and($producto->variantes[0]->codigoSku)->toBe('SKU-SERVICIO');
});
