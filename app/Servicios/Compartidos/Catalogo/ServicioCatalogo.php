<?php

namespace App\Servicios\Compartidos\Catalogo;

use App\Dominio\Catalogo\ProductoDao;
use App\Dominio\Catalogo\ProductoEntidad;
use App\Dominio\Catalogo\VarianteProductoEntidad;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ServicioCatalogo
{
    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ProductoDao $productoDao,
    ) {}

    /**
     * Construye la consulta paginable del catálogo con búsqueda, categoría, marca,
     * rango de precios y orden permitido. Solo considera productos activos y precarga
     * categoría e imágenes para que la vista no ejecute consultas adicionales.
     *
     * @param  array<string, mixed>  $filters  Filtros validados del catálogo.
     * @return Builder<Producto> Consulta Eloquent aún no ejecutada.
     */
    public function query(array $filters): Builder
    {
        $productsQuery = Producto::query()->with(['category', 'images'])->active();
        $searchTerm = trim((string) ($filters['q'] ?? ''));

        if ($searchTerm !== '') {
            $productsQuery->where(function (Builder $query) use ($searchTerm): void {
                $query->where('nombre', 'like', '%'.$searchTerm.'%')
                    ->orWhere('marca', 'like', '%'.$searchTerm.'%');
            });
        }

        if (($categoryId = $filters['categoria'] ?? null) !== null && $categoryId !== '') {
            $productsQuery->where('categoria_id', (int) $categoryId);
        }

        if (($brand = trim((string) ($filters['marca'] ?? ''))) !== '') {
            $productsQuery->where('marca', $brand);
        }

        if (($minimumPrice = $filters['min'] ?? null) !== null && $minimumPrice !== '') {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) >= ?', [(float) $minimumPrice]);
        }

        if (($maximumPrice = $filters['max'] ?? null) !== null && $maximumPrice !== '') {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) <= ?', [(float) $maximumPrice]);
        }

        [$field, $direction] = match ($filters['orden'] ?? null) {
            'precio_asc' => ['precio', 'asc'],
            'precio_desc' => ['precio', 'desc'],
            'nuevo' => ['creado_en', 'desc'],
            default => ['nombre', 'asc'],
        };

        return $productsQuery->orderBy($field, $direction)->orderBy('id');
    }

    public function sincronizarImagenPortada(Producto $producto): void
    {
        $rutaImagen = $producto->url_imagen;

        if (! is_string($rutaImagen) || trim($rutaImagen) === '') {
            return;
        }

        $this->productoDao->sincronizarImagenPortada((int) $producto->id, $rutaImagen);
    }

    /**
     * Recupera hasta cuatro productos activos relacionados por categoría o marca.
     * Excluye el producto actual, prioriza coincidencias de marca y ordena por oferta.
     *
     * @param  Producto  $producto  Producto cuyo contexto se usa para buscar relacionados.
     * @return Collection<int, Producto> Productos relacionados con sus relaciones cargadas.
     */
    public function relacionados(Producto $producto): Collection
    {
        $query = Producto::query()
            ->with(['category', 'images', 'variants'])
            ->active()
            ->where('id', '<>', $producto->getKey())
            ->where(function (Builder $query) use ($producto): void {
                $query->where('categoria_id', $producto->categoria_id)
                    ->orWhere('marca', $producto->marca);
            })
            ->orderByRaw('CASE WHEN marca = ? THEN 0 ELSE 1 END', [$producto->marca])
            ->orderByDesc('precio_oferta')
            ->orderByDesc('id')
            ->limit(4);

        $relatedProducts = $query->get();

        if ($relatedProducts->isNotEmpty()) {
            return $relatedProducts;
        }

        return Producto::query()
            ->with(['category', 'images', 'variants'])
            ->active()
            ->where('id', '<>', $producto->getKey())
            ->orderByDesc('creado_en')
            ->orderByDesc('id')
            ->limit(4)
            ->get();
    }

    /**
     * Prepara los valores visuales del comparador sin trasladar reglas de imagen
     * o puntuación a la vista.
     *
     * @param  Collection<int, Producto>  $products
     * @return Collection<int, Producto>
     */
    public function prepararComparacion(Collection $products): Collection
    {
        return $products->map(static function (Producto $product): Producto {
            $product->setAttribute('comparison_image', $product->url_imagen_principal ?: asset('assets/cell-legacy/img/marca/logo.jpeg'));
            $product->setAttribute('comparison_score', (int) ($product->intelligenceProfile?->puntuacion_valor ?? 70));

            return $product;
        });
    }

    public function agregarDisponibilidadVariantes(Collection $variantes): void
    {
        $ids = $variantes->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        if ($ids === []) {
            return;
        }

        $saldos = DB::table('existencias_almacen as existencia')
            ->join('almacenes as almacen', 'almacen.id', '=', 'existencia.almacen_id')
            ->join('sedes as sede', 'sede.id', '=', 'existencia.sede_id')
            ->join('empresas as empresa', 'empresa.id', '=', 'existencia.empresa_id')
            ->whereIn('existencia.variante_id', $ids)
            ->where('existencia.cantidad_fisica', '>', DB::raw('existencia.cantidad_reservada'))
            ->where('almacen.activo', true)
            ->where('almacen.permite_venta', true)
            ->where('sede.activa', true)
            ->where('empresa.activo', true)
            ->select('existencia.variante_id')
            ->selectRaw('SUM(existencia.cantidad_fisica - existencia.cantidad_reservada) AS unidades')
            ->groupBy('existencia.variante_id')
            ->pluck('unidades', 'variante_id');

        $variantes->each(function (Model $variante) use ($saldos): void {
            $variante->setAttribute('existencias_disponibles', max(0, (int) floor((float) ($saldos[$variante->id] ?? 0))));
            $variante->setAttribute('url_imagen_principal', $variante->images->first()?->ruta_imagen);
        });
    }

    public function crearProductoConVariante(
        ProductoEntidad $producto,
        string $codigoSku,
        string $nombreColor,
        int $existencias,
    ): ProductoEntidad {
        $dao = $producto->empresaId === null
            ? $this->productoDao
            : $this->productoDao->conEmpresa($producto->empresaId);

        return $dao->enTransaccion(function (ProductoDao $dao) use ($producto, $codigoSku, $nombreColor, $existencias): ProductoEntidad {
            $creado = $dao->crear($producto);
            $dao->crearVariante(new VarianteProductoEntidad(
                id: null,
                productoId: (int) $creado->id,
                codigoSku: $codigoSku,
                codigoBarras: null,
                nombreColor: $nombreColor,
                codigoColor: null,
                existencias: $existencias,
                controlaSerie: false,
                estado: 'activo',
            ));

            $resultado = $dao->buscarPorId((int) $creado->id);
            if ($resultado === null) {
                throw new \RuntimeException('El producto creado no pudo ser recuperado con su variante.');
            }

            return $resultado;
        });
    }

    /**
     * @return array{categories: Collection, brands: Collection}
     */
    public function filterOptions(): array
    {
        return [
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Producto::query()
                ->active()
                ->whereNotNull('marca')
                ->where('marca', '<>', '')
                ->distinct()
                ->orderBy('marca')
                ->pluck('marca'),
        ];
    }

    /** @return Builder<Producto> */
    public function conExistenciasPotenciales(Builder $productos): Builder
    {
        return $productos->whereExists(function (QueryBuilder $consulta): void {
            $consulta->selectRaw('1')->from('existencias_almacen as existencia')
                ->join('almacenes as almacen', 'almacen.id', '=', 'existencia.almacen_id')
                ->join('sedes as sede', 'sede.id', '=', 'existencia.sede_id')
                ->join('empresas as empresa', 'empresa.id', '=', 'existencia.empresa_id')
                ->whereColumn('existencia.producto_id', 'productos.id')
                ->whereColumn('existencia.empresa_id', 'productos.empresa_id')
                ->whereColumn('almacen.sede_id', 'sede.id')
                ->whereColumn('sede.empresa_id', 'empresa.id')
                ->whereColumn('existencia.cantidad_fisica', '>', 'existencia.cantidad_reservada')
                ->where('almacen.activo', true)->where('almacen.permite_venta', true)
                ->where('sede.activa', true)->where('empresa.activo', true);
        });
    }

    /**
     * @param  Collection<int, Producto>  $productos
     */
    public function agregarDisponibilidad(Collection $productos, ?User $usuario): void
    {
        $ids = $productos->pluck('id')->all();
        if ($ids === []) {
            return;
        }

        $disponibles = array_fill_keys($ids, 0);
        if ($usuario !== null && (($usuario->can('gestion.cotizaciones.consultar') || $usuario->can('gestion.pedidos.consultar')) || $this->autorizacion->allows($usuario, 'gestion.pedidos.crear'))) {
            try {
                $disponibles = array_replace($disponibles, $this->inventario->disponibilidadParaOperacion($usuario, $ids));
            } catch (HttpExceptionInterface) {
                $disponibles = array_fill_keys($ids, 0);
            }
        } else {
            $serializados = DB::table('variantes_productos')->whereIn('producto_id', $ids)
                ->where('controla_serie', true)->pluck('producto_id')
                ->merge(DB::table('dispositivos')->whereIn('producto_id', $ids)->pluck('producto_id'))
                ->map(fn (mixed $id): int => (int) $id)->unique()->all();
            $saldos = DB::table('existencias_almacen as existencia')
                ->join('productos as producto', 'producto.id', '=', 'existencia.producto_id')
                ->join('almacenes as almacen', 'almacen.id', '=', 'existencia.almacen_id')
                ->join('sedes as sede', 'sede.id', '=', 'existencia.sede_id')
                ->join('empresas as empresa', 'empresa.id', '=', 'existencia.empresa_id')
                ->whereIn('existencia.producto_id', $ids)
                ->whereColumn('producto.empresa_id', 'existencia.empresa_id')
                ->whereColumn('almacen.sede_id', 'existencia.sede_id')
                ->whereColumn('sede.empresa_id', 'existencia.empresa_id')
                ->where('producto.activo', true)->where('almacen.activo', true)
                ->where('almacen.permite_venta', true)->where('sede.activa', true)->where('empresa.activo', true)
                ->select('existencia.producto_id', 'existencia.almacen_id')
                ->selectRaw('COUNT(*) AS cantidad_saldos, SUM(existencia.cantidad_fisica - existencia.cantidad_reservada) AS unidades')
                ->groupBy('existencia.producto_id', 'existencia.almacen_id')->get();
            foreach ($saldos as $saldo) {
                $productoId = (int) $saldo->producto_id;
                if ((int) $saldo->cantidad_saldos !== 1 || in_array($productoId, $serializados, true)) {
                    continue;
                }
                $unidades = (float) $saldo->unidades;
                if ($unidades >= 0 && $unidades === floor($unidades)) {
                    $disponibles[$productoId] = max($disponibles[$productoId], (int) $unidades);
                }
            }
        }

        $productos->each(fn (Producto $producto) => $producto->setAttribute('existencias_disponibles', $disponibles[$producto->id] ?? 0));
    }
}
