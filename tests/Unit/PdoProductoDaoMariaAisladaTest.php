<?php

use App\Dominio\Catalogo\ProductoEntidad;
use App\Dominio\Catalogo\VarianteProductoEntidad;
use App\Infraestructura\Persistencia\Pdo\PdoConexion;
use App\Infraestructura\Persistencia\Pdo\PdoProductoDao;
use Tests\TestCase;

uses(TestCase::class);

it('persiste producto y variante usando exclusivamente la instancia MariaDB aislada', function (): void {
    $pipe = getenv('TECHNOLOGY_CELL_QA_PIPE');
    $root = getenv('TECHNOLOGY_CELL_QA_ROOT');
    $database = getenv('TECHNOLOGY_CELL_QA_DATABASE');
    if (! is_string($pipe) || ! is_string($root) || ! is_string($database)) {
        $this->markTestSkipped('Requiere la instancia MariaDB descartable identificada.');
    }

    $temporaryDirectory = realpath(sys_get_temp_dir());
    $resolvedRoot = realpath($root);
    if ($temporaryDirectory === false || $resolvedRoot === false
        || ! preg_match('/\Atc_ventas_[a-f0-9]{32}\z/', $pipe)
        || ! preg_match('/\Atc_ventas_laravel_[a-z0-9]+\z/', $database)
        || ! str_starts_with(strtolower($resolvedRoot), strtolower($temporaryDirectory.DIRECTORY_SEPARATOR.'technology_cell_ventas_pipe_'))) {
        throw new RuntimeException('La conexión indicada no es una instancia temporal autorizada.');
    }

    $pdo = new PDO('mysql:host=.;port=1;unix_socket=\\\\.\\pipe\\'.$pipe.';dbname='.$database, 'root', '', [PDO::ATTR_TIMEOUT => 1]);
    $identity = $pdo->query('SELECT @@version, @@datadir, @@skip_networking, @@socket')->fetch(PDO::FETCH_NUM);
    if (! is_array($identity) || $identity[0] !== '10.4.32-MariaDB'
        || realpath((string) $identity[1]) !== realpath($resolvedRoot.DIRECTORY_SEPARATOR.'datos')
        || (int) $identity[2] !== 1 || $identity[3] !== $pipe) {
        throw new RuntimeException('La identidad MariaDB no corresponde a la instancia descartable.');
    }

    $category = $pdo->query('SELECT id, nombre, empresa_id FROM categorias ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if (! is_array($category)) {
        $categoryName = 'Categoria DAO QA '.bin2hex(random_bytes(4));
        $statement = $pdo->prepare('INSERT INTO categorias (nombre, empresa_id) VALUES (?, NULL)');
        $statement->execute([$categoryName]);
        $category = $pdo->query('SELECT id, nombre, empresa_id FROM categorias ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    }
    if (! is_array($category)) {
        throw new RuntimeException('No se pudo preparar una categoria valida para la prueba DAO.');
    }

    $pdo->beginTransaction();
    try {
        $dao = new PdoProductoDao(new PdoConexion($pdo));
        $product = $dao->crear(new ProductoEntidad(
            id: null, marca: 'DAO QA', nombre: 'Producto DAO QA '.bin2hex(random_bytes(4)),
            categoriaId: (int) $category['id'], categoria: (string) $category['nombre'], precio: '10.00',
            precioOferta: null, activo: true, empresaId: $category['empresa_id'] !== null ? (int) $category['empresa_id'] : null,
        ));
        $variant = $dao->crearVariante(new VarianteProductoEntidad(
            id: null, productoId: (int) $product->id, codigoSku: 'DAO-QA-'.bin2hex(random_bytes(3)),
            codigoBarras: null, nombreColor: 'Negro', codigoColor: null, existencias: 2,
            controlaSerie: false, estado: 'activo',
        ));

        $loaded = $dao->buscarPorId((int) $product->id);
        expect($loaded?->variantes)->toHaveCount(1)
            ->and($variant->productoId)->toBe($product->id);
    } finally {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
});
