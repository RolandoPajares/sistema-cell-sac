<?php

namespace App\Infraestructura\Persistencia\Pdo;

use App\Dominio\Catalogo\ProductoDao;
use App\Dominio\Catalogo\ProductoEntidad;
use App\Dominio\Catalogo\ResultadoPaginado;
use App\Dominio\Catalogo\VarianteProductoEntidad;
use Closure;
use DomainException;
use PDO;
use RuntimeException;

/**
 * Persiste productos y variantes mediante consultas PDO preparadas.
 *
 * Centraliza el mapeo entre filas de MariaDB y las entidades del dominio, y
 * agrupa las operaciones de escritura dentro de transacciones.
 */
final class PdoProductoDao implements ProductoDao
{
    private readonly PdoHidratador $hidratador;

    /**
     * Recibe la conexión PDO encapsulada que ejecutará las consultas del DAO.
     *
     * @param  PdoConexion  $conexion  Conexión configurada para preparar consultas y controlar transacciones.
     */
    public function __construct(
        private readonly PdoConexion $conexion,
        private readonly ?int $empresaId = null,
    ) {
        $this->hidratador = new PdoHidratador;

        if ($this->empresaId !== null && $this->empresaId < 1) {
            throw new DomainException('El alcance empresarial debe ser positivo.');
        }
    }

    public function conEmpresa(int $empresaId): ProductoDao
    {
        return new self($this->conexion, $empresaId);
    }

    /**
     * Ejecuta una operación del DAO usando la transacción administrada por la conexión.
     *
     * La devolución de la operación se propaga sin modificarla; las excepciones
     * provocan rollback cuando esta llamada inició la transacción.
     *
     * @param  Closure(self): mixed  $operacion  Callback que recibe este DAO.
     * @return mixed Resultado producido por el callback.
     */
    public function enTransaccion(Closure $operacion): mixed
    {
        return $this->conexion->ejecutarEnTransaccion(fn (): mixed => $operacion($this));
    }

    /**
     * Busca un producto por identificador y lo mapea junto con sus variantes.
     *
     * Utiliza un SELECT preparado y devuelve null si no existe la fila. Las
     * variantes solo se consultan cuando se solicita su inclusión.
     *
     * @param  int  $id  Identificador del producto.
     * @param  bool  $incluirVariantes  Indica si debe cargar las variantes asociadas.
     * @return ProductoEntidad|null Entidad reconstruida o null cuando no existe.
     */
    public function buscarPorId(int $id, bool $incluirVariantes = true): ?ProductoEntidad
    {
        $sql = 'SELECT id, marca, nombre, categoria_id, categoria, precio, precio_oferta, activo, empresa_id FROM productos WHERE id = :id';
        $parametros = ['id' => $id];
        if ($this->empresaId !== null) {
            $sql .= ' AND empresa_id = :empresa_id';
            $parametros['empresa_id'] = $this->empresaId;
        }
        $consulta = $this->conexion->preparar($sql.' LIMIT 1');
        $consulta->execute($parametros);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($fila === false) {
            return null;
        }

        $variantes = $incluirVariantes ? $this->buscarVariantes((int) $fila['id']) : [];

        return $this->productoDesdeFila($fila, $variantes);
    }

    /**
     * Lista productos sin aceptar identificadores SQL provenientes del cliente.
     * Los filtros se convierten en parámetros y el orden está fijado por código.
     *
     * @param  array{buscar?: string, activo?: bool|null, empresa_id?: int|null}  $filtros
     * @return ResultadoPaginado<ProductoEntidad>
     */
    public function listar(array $filtros = [], int $pagina = 1, int $porPagina = 20): ResultadoPaginado
    {
        $pagina = max(1, $pagina);
        $porPagina = min(100, max(1, $porPagina));
        $where = [];
        $parametros = [];

        $buscar = trim((string) ($filtros['buscar'] ?? ''));
        if ($buscar !== '') {
            $where[] = '(marca LIKE :buscar_marca OR nombre LIKE :buscar_nombre)';
            $parametros['buscar_marca'] = "%{$buscar}%";
            $parametros['buscar_nombre'] = "%{$buscar}%";
        }
        if (array_key_exists('activo', $filtros) && $filtros['activo'] !== null) {
            $where[] = 'activo = :activo';
            $parametros['activo'] = $filtros['activo'] ? 1 : 0;
        }
        if (array_key_exists('empresa_id', $filtros) && $filtros['empresa_id'] !== null) {
            if ($this->empresaId !== null && (int) $filtros['empresa_id'] !== $this->empresaId) {
                throw new DomainException('El filtro empresarial no coincide con el alcance del DAO.');
            }
            $where[] = 'empresa_id = :empresa_id';
            $parametros['empresa_id'] = (int) $filtros['empresa_id'];
        } elseif ($this->empresaId !== null) {
            $where[] = 'empresa_id = :empresa_id';
            $parametros['empresa_id'] = $this->empresaId;
        }

        $whereSql = $where === [] ? '' : ' WHERE '.implode(' AND ', $where);
        $count = $this->conexion->preparar("SELECT COUNT(*) FROM productos{$whereSql}");
        $count->execute($parametros);
        $total = (int) $count->fetchColumn();
        $offset = ($pagina - 1) * $porPagina;

        $consulta = $this->conexion->preparar("SELECT id, marca, nombre, categoria_id, categoria, precio, precio_oferta, activo, empresa_id FROM productos{$whereSql} ORDER BY id DESC LIMIT :limite OFFSET :desplazamiento");
        foreach ($parametros as $nombre => $valor) {
            $consulta->bindValue(':'.$nombre, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $consulta->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $consulta->bindValue(':desplazamiento', $offset, PDO::PARAM_INT);
        $consulta->execute();

        $items = array_map(
            fn (array $fila): ProductoEntidad => $this->productoDesdeFila($fila, []),
            $consulta->fetchAll(PDO::FETCH_ASSOC),
        );

        return new ResultadoPaginado($items, $total, $porPagina, $pagina);
    }

    /**
     * Valida e inserta un producto y devuelve la entidad recién persistida.
     *
     * El INSERT y la posterior recuperación por el último identificador se
     * ejecutan en una misma transacción; una entidad no recuperable produce
     * RuntimeException y provoca rollback.
     *
     * @param  ProductoEntidad  $producto  Datos del producto que se insertará.
     * @return ProductoEntidad Producto creado, incluyendo su identificador.
     *
     * @throws DomainException Si faltan datos obligatorios del producto.
     * @throws RuntimeException Si el registro insertado no puede recuperarse.
     */
    public function crear(ProductoEntidad $producto): ProductoEntidad
    {
        $this->validarProducto($producto);
        $this->validarEmpresa($producto->empresaId);

        return $this->conexion->ejecutarEnTransaccion(function () use ($producto): ProductoEntidad {
            $consulta = $this->conexion->preparar('INSERT INTO productos (marca, nombre, categoria_id, categoria, precio, precio_oferta, activo, empresa_id) VALUES (:marca, :nombre, :categoria_id, :categoria, :precio, :precio_oferta, :activo, :empresa_id)');
            $consulta->execute($this->hidratador->valoresSinClave($producto, $this->mapaProducto()));
            $creado = $this->buscarPorId($this->conexion->ultimoId());
            if ($creado === null) {
                throw new RuntimeException('El producto insertado no pudo ser recuperado.');
            }

            return $creado;
        });
    }

    /**
     * Valida y actualiza un producto existente, devolviendo su estado persistido.
     *
     * La operación exige identificador, ejecuta el UPDATE y vuelve a leer la
     * entidad dentro de la misma transacción para confirmar el resultado.
     *
     * @param  ProductoEntidad  $producto  Producto con identificador y nuevos datos.
     * @return ProductoEntidad Producto actualizado.
     *
     * @throws DomainException Si no hay identificador o faltan datos obligatorios.
     * @throws RuntimeException Si el producto no existe después del UPDATE.
     */
    public function actualizar(ProductoEntidad $producto): ProductoEntidad
    {
        if ($producto->id === null) {
            throw new DomainException('No se puede actualizar un producto sin identificador.');
        }
        $this->validarProducto($producto);
        $this->validarEmpresa($producto->empresaId);

        return $this->enTransaccion(function () use ($producto): ProductoEntidad {
            $sql = 'UPDATE productos SET marca = :marca, nombre = :nombre, categoria_id = :categoria_id, categoria = :categoria, precio = :precio, precio_oferta = :precio_oferta, activo = :activo, empresa_id = :empresa_id WHERE id = :id';
            $parametros = $this->hidratador->valoresDesdeEntidad($producto, $this->mapaProducto());
            if ($this->empresaId !== null) {
                $sql .= ' AND empresa_id = :alcance_empresa_id';
                $parametros['alcance_empresa_id'] = $this->empresaId;
            }
            $consulta = $this->conexion->preparar($sql);
            $consulta->execute($parametros);
            $actualizado = $this->buscarPorId($producto->id);
            if ($actualizado === null) {
                throw new RuntimeException('El producto no existe o no pudo actualizarse.');
            }

            return $actualizado;
        });
    }

    /**
     * Valida e inserta una variante asociada a un producto existente.
     *
     * Comprueba la entidad padre dentro de la transacción, ejecuta el INSERT y
     * recupera la variante por el identificador generado.
     *
     * @param  VarianteProductoEntidad  $variante  Datos de la variante a insertar.
     * @return VarianteProductoEntidad Variante persistida.
     *
     * @throws DomainException Si la variante es inválida o el producto no existe.
     * @throws RuntimeException Si la variante creada no puede recuperarse.
     */
    public function crearVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad
    {
        $this->validarVariante($variante);

        return $this->enTransaccion(function () use ($variante): VarianteProductoEntidad {
            if ($this->buscarPorId($variante->productoId, false) === null) {
                throw new DomainException('No se puede crear una variante para un producto inexistente.');
            }

            $consulta = $this->conexion->preparar('INSERT INTO variantes_productos (producto_id, codigo_sku, codigo_barras, nombre_color, codigo_color, existencias, controla_serie, estado) VALUES (:producto_id, :codigo_sku, :codigo_barras, :nombre_color, :codigo_color, :existencias, :controla_serie, :estado)');
            $consulta->execute($this->hidratador->valoresSinClave($variante, $this->mapaVariante()));

            return $this->buscarVariantePorId($this->conexion->ultimoId());
        });
    }

    /**
     * Valida y actualiza todos los datos persistibles de una variante.
     *
     * La operación requiere identificador y se ejecuta dentro de una
     * transacción; después vuelve a consultar la variante actualizada.
     *
     * @param  VarianteProductoEntidad  $variante  Variante con identificador y nuevos datos.
     * @return VarianteProductoEntidad Variante actualizada.
     *
     * @throws DomainException Si no hay identificador o la variante es inválida.
     * @throws RuntimeException Si la variante no puede recuperarse.
     */
    public function actualizarVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad
    {
        if ($variante->id === null) {
            throw new DomainException('No se puede actualizar una variante sin identificador.');
        }
        $this->validarVariante($variante);
        if ($this->empresaId !== null && $this->buscarPorId($variante->productoId, false) === null) {
            throw new DomainException('La variante no puede apuntar a un producto fuera del alcance empresarial.');
        }

        return $this->enTransaccion(function () use ($variante): VarianteProductoEntidad {
            $sql = 'UPDATE variantes_productos SET producto_id = :producto_id, codigo_sku = :codigo_sku, codigo_barras = :codigo_barras, nombre_color = :nombre_color, codigo_color = :codigo_color, existencias = :existencias, controla_serie = :controla_serie, estado = :estado WHERE id = :id';
            $parametros = $this->hidratador->valoresDesdeEntidad($variante, $this->mapaVariante());
            if ($this->empresaId !== null) {
                $sql .= ' AND EXISTS (SELECT 1 FROM productos WHERE productos.id = variantes_productos.producto_id AND productos.empresa_id = :alcance_empresa_id)';
                $parametros['alcance_empresa_id'] = $this->empresaId;
            }
            $consulta = $this->conexion->preparar($sql);
            $consulta->execute($parametros);

            return $this->buscarVariantePorId($variante->id);
        });
    }

    /**
     * Elimina una variante mediante una sentencia DELETE preparada transaccional.
     *
     * @param  int  $id  Identificador de la variante que se eliminará.
     */
    public function eliminarVariante(int $id): void
    {
        $this->enTransaccion(function () use ($id): void {
            $sql = 'DELETE FROM variantes_productos WHERE id = :id';
            $parametros = ['id' => $id];
            if ($this->empresaId !== null) {
                $sql .= ' AND EXISTS (SELECT 1 FROM productos WHERE productos.id = variantes_productos.producto_id AND productos.empresa_id = :empresa_id)';
                $parametros['empresa_id'] = $this->empresaId;
            }
            $consulta = $this->conexion->preparar($sql);
            $consulta->execute($parametros);
        });
    }

    /**
     * Elimina un producto mediante una sentencia DELETE preparada transaccional.
     *
     * @param  int  $id  Identificador del producto que se eliminará.
     */
    public function eliminar(int $id): void
    {
        $this->enTransaccion(function () use ($id): void {
            $sql = 'DELETE FROM productos WHERE id = :id';
            $parametros = ['id' => $id];
            if ($this->empresaId !== null) {
                $sql .= ' AND empresa_id = :empresa_id';
                $parametros['empresa_id'] = $this->empresaId;
            }
            $consulta = $this->conexion->preparar($sql);
            $consulta->execute($parametros);
        });
    }

    /**
     * Registra una imagen como portada del producto sin duplicar su referencia.
     *
     * Si la ruta ya existe la reutiliza; cuando debe priorizarla, incrementa
     * el orden de las demás imágenes y asigna orden cero a la portada. Toda la
     * sincronización se realiza en una transacción y una ruta vacía no escribe.
     *
     * @param  int  $productoId  Identificador del producto propietario.
     * @param  string  $rutaImagen  Ruta almacenada de la imagen.
     *
     * @throws DomainException Si el producto no existe.
     */
    public function sincronizarImagenPortada(int $productoId, string $rutaImagen): void
    {
        if (trim($rutaImagen) === '') {
            return;
        }
        $this->conexion->ejecutarEnTransaccion(function () use ($productoId, $rutaImagen): void {
            if ($this->buscarPorId($productoId, false) === null) {
                throw new DomainException('No se puede sincronizar una imagen para un producto inexistente.');
            }
            $buscar = $this->conexion->preparar('SELECT id, orden FROM referencias_imagenes_productos WHERE producto_id = :producto_id AND ruta_imagen = :ruta_imagen LIMIT 1');
            $buscar->execute(['producto_id' => $productoId, 'ruta_imagen' => $rutaImagen]);
            $imagen = $buscar->fetch(PDO::FETCH_ASSOC);
            $debePriorizar = $imagen === false || (int) $imagen['orden'] !== 0;
            if ($imagen === false) {
                $insertar = $this->conexion->preparar('INSERT INTO referencias_imagenes_productos (producto_id, ruta_imagen, orden, actualizado_en) VALUES (:producto_id, :ruta_imagen, 0, CURRENT_TIMESTAMP)');
                $insertar->execute(['producto_id' => $productoId, 'ruta_imagen' => $rutaImagen]);
                $imagenId = $this->conexion->ultimoId();
            } else {
                $imagenId = (int) $imagen['id'];
            }
            if (! $debePriorizar) {
                $otraPrioritaria = $this->conexion->preparar('SELECT 1 FROM referencias_imagenes_productos WHERE producto_id = :producto_id AND id <> :imagen_id AND orden <= 0 LIMIT 1');
                $otraPrioritaria->execute(['producto_id' => $productoId, 'imagen_id' => $imagenId]);
                $debePriorizar = $otraPrioritaria->fetchColumn() !== false;
            }
            if (! $debePriorizar) {
                return;
            }
            $subir = $this->conexion->preparar('UPDATE referencias_imagenes_productos SET orden = orden + 1 WHERE producto_id = :producto_id AND id <> :imagen_id');
            $subir->execute(['producto_id' => $productoId, 'imagen_id' => $imagenId]);
            $priorizar = $this->conexion->preparar('UPDATE referencias_imagenes_productos SET orden = 0, actualizado_en = CURRENT_TIMESTAMP WHERE id = :id');
            $priorizar->execute(['id' => $imagenId]);
        });
    }

    /** @return list<VarianteProductoEntidad> */
    private function buscarVariantes(int $productoId): array
    {
        $consulta = $this->conexion->preparar('SELECT id, producto_id, codigo_sku, codigo_barras, nombre_color, codigo_color, existencias, controla_serie, estado FROM variantes_productos WHERE producto_id = :producto_id ORDER BY id');
        $consulta->execute(['producto_id' => $productoId]);

        return array_map(fn (array $fila): VarianteProductoEntidad => $this->varianteDesdeFila($fila), $consulta->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Recupera una variante por identificador y la convierte en entidad de dominio.
     *
     * @param  int  $id  Identificador de la variante.
     * @return VarianteProductoEntidad Variante mapeada desde MariaDB.
     *
     * @throws RuntimeException Si no existe una fila con el identificador solicitado.
     */
    private function buscarVariantePorId(int $id): VarianteProductoEntidad
    {
        $consulta = $this->conexion->preparar('SELECT id, producto_id, codigo_sku, codigo_barras, nombre_color, codigo_color, existencias, controla_serie, estado FROM variantes_productos WHERE id = :id LIMIT 1');
        $consulta->execute(['id' => $id]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($fila === false) {
            throw new RuntimeException('La variante no pudo ser recuperada.');
        }

        return $this->varianteDesdeFila($fila);
    }

    /** @param array<string, mixed> $fila @param list<VarianteProductoEntidad> $variantes */
    private function productoDesdeFila(array $fila, array $variantes): ProductoEntidad
    {
        $atributos = $this->hidratador->atributosDesdeFila($fila, $this->mapaProducto());

        return new ProductoEntidad(...$atributos, variantes: $variantes);
    }

    /** @param array<string, mixed> $fila */
    private function varianteDesdeFila(array $fila): VarianteProductoEntidad
    {
        return new VarianteProductoEntidad(...$this->hidratador->atributosDesdeFila($fila, $this->mapaVariante()));
    }

    private function mapaProducto(): PdoMapaEntidad
    {
        return new PdoMapaEntidad('productos', 'id', [
            'id' => 'id', 'marca' => 'marca', 'nombre' => 'nombre', 'categoriaId' => 'categoria_id',
            'categoria' => 'categoria', 'precio' => 'precio', 'precioOferta' => 'precio_oferta',
            'activo' => 'activo', 'empresaId' => 'empresa_id',
        ], [
            'id' => 'int', 'marca' => 'string', 'nombre' => 'string', 'categoriaId' => 'int',
            'categoria' => 'string', 'precio' => 'decimal', 'precioOferta' => 'decimal',
            'activo' => 'bool', 'empresaId' => 'int',
        ]);
    }

    private function mapaVariante(): PdoMapaEntidad
    {
        return new PdoMapaEntidad('variantes_productos', 'id', [
            'id' => 'id', 'productoId' => 'producto_id', 'codigoSku' => 'codigo_sku', 'codigoBarras' => 'codigo_barras',
            'nombreColor' => 'nombre_color', 'codigoColor' => 'codigo_color', 'existencias' => 'existencias',
            'controlaSerie' => 'controla_serie', 'estado' => 'estado',
        ], [
            'id' => 'int', 'productoId' => 'int', 'codigoSku' => 'string', 'codigoBarras' => 'string',
            'nombreColor' => 'string', 'codigoColor' => 'string', 'existencias' => 'int',
            'controlaSerie' => 'bool', 'estado' => 'string',
        ]);
    }

    /**
     * Comprueba los campos obligatorios y la categoría positiva de un producto.
     *
     * @param  ProductoEntidad  $producto  Producto que se validará antes de escribir.
     *
     * @throws DomainException Si falta marca, nombre, categoría, precio o categoría válida.
     */
    private function validarProducto(ProductoEntidad $producto): void
    {
        if ($producto->marca === '' || $producto->nombre === '' || $producto->categoria === '' || $producto->precio === '' || $producto->categoriaId < 1) {
            throw new DomainException('Marca, nombre, categoría, precio y categoría válida son obligatorios.');
        }
    }

    /**
     * Comprueba producto, color, existencias y estado requeridos por una variante.
     *
     * @param  VarianteProductoEntidad  $variante  Variante que se validará antes de escribir.
     *
     * @throws DomainException Si falta un dato obligatorio o las existencias son negativas.
     */
    private function validarVariante(VarianteProductoEntidad $variante): void
    {
        if ($variante->productoId < 1 || $variante->nombreColor === '' || $variante->existencias < 0 || $variante->estado === '') {
            throw new DomainException('Producto, color, existencias y estado de variante son obligatorios y válidos.');
        }
    }

    private function validarEmpresa(?int $empresaId): void
    {
        if ($this->empresaId !== null && $empresaId !== $this->empresaId) {
            throw new DomainException('El producto no pertenece al alcance empresarial del DAO.');
        }
    }
}
