<?php

namespace App\Infraestructura\Persistencia\Pdo;

use App\Dominio\Catalogo\ProductoDao;
use App\Dominio\Catalogo\ProductoEntidad;
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
    /**
     * Recibe la conexión PDO encapsulada que ejecutará las consultas del DAO.
     *
     * @param PdoConexion $conexion Conexión configurada para preparar consultas y controlar transacciones.
     */
    public function __construct(private readonly PdoConexion $conexion) {}

    /**
     * Ejecuta una operación del DAO usando la transacción administrada por la conexión.
     *
     * La devolución de la operación se propaga sin modificarla; las excepciones
     * provocan rollback cuando esta llamada inició la transacción.
     *
     * @param Closure(self): mixed $operacion Callback que recibe este DAO.
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
     * @param int $id Identificador del producto.
     * @param bool $incluirVariantes Indica si debe cargar las variantes asociadas.
     * @return ProductoEntidad|null Entidad reconstruida o null cuando no existe.
     */
    public function buscarPorId(int $id, bool $incluirVariantes = true): ?ProductoEntidad
    {
        $consulta = $this->conexion->preparar('SELECT id, marca, nombre, categoria_id, categoria, precio, precio_oferta, activo, empresa_id FROM productos WHERE id = :id LIMIT 1');
        $consulta->execute(['id' => $id]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($fila === false) {
            return null;
        }

        $variantes = $incluirVariantes ? $this->buscarVariantes((int) $fila['id']) : [];

        return new ProductoEntidad(
            id: (int) $fila['id'], marca: (string) $fila['marca'], nombre: (string) $fila['nombre'],
            categoriaId: (int) $fila['categoria_id'], categoria: (string) $fila['categoria'],
            precio: (string) $fila['precio'], precioOferta: $fila['precio_oferta'] !== null ? (string) $fila['precio_oferta'] : null,
            activo: (bool) $fila['activo'], empresaId: $fila['empresa_id'] !== null ? (int) $fila['empresa_id'] : null,
            variantes: $variantes,
        );
    }

    /**
     * Valida e inserta un producto y devuelve la entidad recién persistida.
     *
     * El INSERT y la posterior recuperación por el último identificador se
     * ejecutan en una misma transacción; una entidad no recuperable produce
     * RuntimeException y provoca rollback.
     *
     * @param ProductoEntidad $producto Datos del producto que se insertará.
     * @return ProductoEntidad Producto creado, incluyendo su identificador.
     * @throws DomainException Si faltan datos obligatorios del producto.
     * @throws RuntimeException Si el registro insertado no puede recuperarse.
     */
    public function crear(ProductoEntidad $producto): ProductoEntidad
    {
        $this->validarProducto($producto);

        return $this->conexion->ejecutarEnTransaccion(function () use ($producto): ProductoEntidad {
            $consulta = $this->conexion->preparar('INSERT INTO productos (marca, nombre, categoria_id, categoria, precio, precio_oferta, activo, empresa_id) VALUES (:marca, :nombre, :categoria_id, :categoria, :precio, :precio_oferta, :activo, :empresa_id)');
            $consulta->execute([
                'marca' => $producto->marca, 'nombre' => $producto->nombre, 'categoria_id' => $producto->categoriaId,
                'categoria' => $producto->categoria, 'precio' => $producto->precio, 'precio_oferta' => $producto->precioOferta,
                'activo' => $producto->activo ? 1 : 0, 'empresa_id' => $producto->empresaId,
            ]);
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
     * @param ProductoEntidad $producto Producto con identificador y nuevos datos.
     * @return ProductoEntidad Producto actualizado.
     * @throws DomainException Si no hay identificador o faltan datos obligatorios.
     * @throws RuntimeException Si el producto no existe después del UPDATE.
     */
    public function actualizar(ProductoEntidad $producto): ProductoEntidad
    {
        if ($producto->id === null) {
            throw new DomainException('No se puede actualizar un producto sin identificador.');
        }
        $this->validarProducto($producto);

        return $this->enTransaccion(function () use ($producto): ProductoEntidad {
            $consulta = $this->conexion->preparar('UPDATE productos SET marca = :marca, nombre = :nombre, categoria_id = :categoria_id, categoria = :categoria, precio = :precio, precio_oferta = :precio_oferta, activo = :activo, empresa_id = :empresa_id WHERE id = :id');
            $consulta->execute([
                'id' => $producto->id, 'marca' => $producto->marca, 'nombre' => $producto->nombre,
                'categoria_id' => $producto->categoriaId, 'categoria' => $producto->categoria, 'precio' => $producto->precio,
                'precio_oferta' => $producto->precioOferta, 'activo' => $producto->activo ? 1 : 0, 'empresa_id' => $producto->empresaId,
            ]);
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
     * @param VarianteProductoEntidad $variante Datos de la variante a insertar.
     * @return VarianteProductoEntidad Variante persistida.
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
            $consulta->execute([
                'producto_id' => $variante->productoId, 'codigo_sku' => $variante->codigoSku,
                'codigo_barras' => $variante->codigoBarras, 'nombre_color' => $variante->nombreColor,
                'codigo_color' => $variante->codigoColor, 'existencias' => $variante->existencias,
                'controla_serie' => $variante->controlaSerie ? 1 : 0, 'estado' => $variante->estado,
            ]);

            return $this->buscarVariantePorId($this->conexion->ultimoId());
        });
    }

    /**
     * Valida y actualiza todos los datos persistibles de una variante.
     *
     * La operación requiere identificador y se ejecuta dentro de una
     * transacción; después vuelve a consultar la variante actualizada.
     *
     * @param VarianteProductoEntidad $variante Variante con identificador y nuevos datos.
     * @return VarianteProductoEntidad Variante actualizada.
     * @throws DomainException Si no hay identificador o la variante es inválida.
     * @throws RuntimeException Si la variante no puede recuperarse.
     */
    public function actualizarVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad
    {
        if ($variante->id === null) {
            throw new DomainException('No se puede actualizar una variante sin identificador.');
        }
        $this->validarVariante($variante);

        return $this->enTransaccion(function () use ($variante): VarianteProductoEntidad {
            $consulta = $this->conexion->preparar('UPDATE variantes_productos SET producto_id = :producto_id, codigo_sku = :codigo_sku, codigo_barras = :codigo_barras, nombre_color = :nombre_color, codigo_color = :codigo_color, existencias = :existencias, controla_serie = :controla_serie, estado = :estado WHERE id = :id');
            $consulta->execute([
                'id' => $variante->id, 'producto_id' => $variante->productoId, 'codigo_sku' => $variante->codigoSku,
                'codigo_barras' => $variante->codigoBarras, 'nombre_color' => $variante->nombreColor,
                'codigo_color' => $variante->codigoColor, 'existencias' => $variante->existencias,
                'controla_serie' => $variante->controlaSerie ? 1 : 0, 'estado' => $variante->estado,
            ]);

            return $this->buscarVariantePorId($variante->id);
        });
    }

    /**
     * Elimina una variante mediante una sentencia DELETE preparada transaccional.
     *
     * @param int $id Identificador de la variante que se eliminará.
     */
    public function eliminarVariante(int $id): void
    {
        $this->enTransaccion(function () use ($id): void {
            $consulta = $this->conexion->preparar('DELETE FROM variantes_productos WHERE id = :id');
            $consulta->execute(['id' => $id]);
        });
    }

    /**
     * Elimina un producto mediante una sentencia DELETE preparada transaccional.
     *
     * @param int $id Identificador del producto que se eliminará.
     */
    public function eliminar(int $id): void
    {
        $this->enTransaccion(function () use ($id): void {
            $consulta = $this->conexion->preparar('DELETE FROM productos WHERE id = :id');
            $consulta->execute(['id' => $id]);
        });
    }

    /**
     * Registra una imagen como portada del producto sin duplicar su referencia.
     *
     * Si la ruta ya existe la reutiliza; cuando debe priorizarla, incrementa
     * el orden de las demás imágenes y asigna orden cero a la portada. Toda la
     * sincronización se realiza en una transacción y una ruta vacía no escribe.
     *
     * @param int $productoId Identificador del producto propietario.
     * @param string $rutaImagen Ruta almacenada de la imagen.
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

        return array_map(fn (array $fila): VarianteProductoEntidad => new VarianteProductoEntidad(
            id: (int) $fila['id'], productoId: (int) $fila['producto_id'], codigoSku: $fila['codigo_sku'],
            codigoBarras: $fila['codigo_barras'], nombreColor: (string) $fila['nombre_color'], codigoColor: $fila['codigo_color'],
            existencias: (int) $fila['existencias'], controlaSerie: (bool) $fila['controla_serie'], estado: (string) $fila['estado'],
        ), $consulta->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Recupera una variante por identificador y la convierte en entidad de dominio.
     *
     * @param int $id Identificador de la variante.
     * @return VarianteProductoEntidad Variante mapeada desde MariaDB.
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

        return new VarianteProductoEntidad(
            id: (int) $fila['id'], productoId: (int) $fila['producto_id'], codigoSku: $fila['codigo_sku'],
            codigoBarras: $fila['codigo_barras'], nombreColor: (string) $fila['nombre_color'], codigoColor: $fila['codigo_color'],
            existencias: (int) $fila['existencias'], controlaSerie: (bool) $fila['controla_serie'], estado: (string) $fila['estado'],
        );
    }

    /**
     * Comprueba los campos obligatorios y la categoría positiva de un producto.
     *
     * @param ProductoEntidad $producto Producto que se validará antes de escribir.
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
     * @param VarianteProductoEntidad $variante Variante que se validará antes de escribir.
     * @throws DomainException Si falta un dato obligatorio o las existencias son negativas.
     */
    private function validarVariante(VarianteProductoEntidad $variante): void
    {
        if ($variante->productoId < 1 || $variante->nombreColor === '' || $variante->existencias < 0 || $variante->estado === '') {
            throw new DomainException('Producto, color, existencias y estado de variante son obligatorios y válidos.');
        }
    }
}
