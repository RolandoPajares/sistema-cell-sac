<?php

namespace App\Servicios\Compartidos\Catalogo;

use App\Dominio\Catalogo\ProductoDao;
use App\Dominio\Catalogo\ProductoEntidad;
use App\Dominio\Catalogo\VarianteProductoEntidad;
use App\Models\Catalogo\Atributo;
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
use Illuminate\Support\Facades\Schema;
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
    public function query(array $filters, ?int $excludedAttributeId = null): Builder
    {
        // Construye la consulta del catalogo aplicando busqueda, marca y orden.
        // Prepara en $productsQuery la consulta que obtiene o modifica los datos necesarios.
        // ORM: inicia la consulta de productos, carga relaciones y filtra los activos.
        $productsQuery = Producto::query()->with(['category', 'images'])->active();
        // Guarda en $searchTerm el resultado necesario para continuar la operacion.
        $searchTerm = trim((string) ($filters['q'] ?? ''));

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($searchTerm !== '') {
            // ORM: agrega la busqueda por nombre o marca usando el constructor de consultas.
            $productsQuery->where(function (Builder $query) use ($searchTerm): void {
                $query->where('nombre', 'like', '%'.$searchTerm.'%')
                    ->orWhere('marca', 'like', '%'.$searchTerm.'%');
            });
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (($categoryId = $filters['categoria'] ?? null) !== null && $categoryId !== '') {
            $productsQuery->where('categoria_id', (int) $categoryId);
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        $brands = $this->listaDeValores($filters['marca'] ?? null);
        if ($brands !== []) {
            $productsQuery->whereIn('marca', $brands);
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (($minimumPrice = $filters['min'] ?? null) !== null && $minimumPrice !== '') {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) >= ?', [(float) $minimumPrice]);
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (($maximumPrice = $filters['max'] ?? null) !== null && $maximumPrice !== '') {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) <= ?', [(float) $maximumPrice]);
        }

        if ($this->eavDisponible()) {
            $this->aplicarFiltrosDeAtributos($productsQuery, $filters['atributos'] ?? [], $excludedAttributeId);
        }

        return match ($filters['orden'] ?? null) {
            'precio_asc' => $productsQuery->orderByRaw('COALESCE(precio_oferta, precio) ASC')->orderBy('id'),
            'precio_desc' => $productsQuery->orderByRaw('COALESCE(precio_oferta, precio) DESC')->orderBy('id'),
            'nuevo' => $productsQuery->orderByDesc('creado_en')->orderByDesc('id'),
            default => $productsQuery->orderBy('nombre')->orderBy('id'),
        };
    }

    public function sincronizarImagenPortada(Producto $producto): void
    {
        // Selecciona y guarda la imagen principal visible del producto.
        // Guarda en $rutaImagen el resultado necesario para continuar la operacion.
        $rutaImagen = $producto->url_imagen;

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
        // Busca productos relacionados por marca, categoria o caracteristicas.
        // Prepara en $query la consulta que obtiene o modifica los datos necesarios.
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

        // Guarda en $relatedProducts el resultado de la consulta ejecutada.
        $relatedProducts = $query->get();

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($relatedProducts->isNotEmpty()) {
            return $relatedProducts;
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
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
        // Prepara los datos comunes que necesita la comparacion de productos.
        return $products->map(static function (Producto $product): Producto {
            $product->setAttribute('comparison_image', $product->url_imagen_principal ?: asset('assets/cell-legacy/img/marca/logo.jpeg'));
            $product->setAttribute('comparison_score', (int) ($product->perfilInteligencia?->puntuacion_valor ?? 70));

            return $product;
        });
    }

    public function agregarDisponibilidadVariantes(Collection $variantes): void
    {
        // Calcula la disponibilidad acumulada de cada variante.
        $ids = $variantes->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($ids === []) {
            return;
        }

        // Prepara en $saldos la consulta que obtiene o modifica los datos necesarios.
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
            // Guarda en $creado el resultado necesario para continuar la operacion.
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

            // Guarda en $resultado el resultado necesario para continuar la operacion.
            $resultado = $dao->buscarPorId((int) $creado->id);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($resultado === null) {
                throw new \RuntimeException('El producto creado no pudo ser recuperado con su variante.');
            }

            return $resultado;
        });
    }

    /**
     * @return array{categories: Collection, brands: Collection, attributes: Collection}
     */
    public function filterOptions(): array
    {
        // Devuelve las opciones disponibles para filtrar el catalogo.
        return [
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Producto::query()
                ->active()
                ->whereNotNull('marca')
                ->where('marca', '<>', '')
                ->distinct()
                ->orderBy('marca')
                ->pluck('marca'),
            'attributes' => $this->eavDisponible()
                ? Atributo::query()->where('activo', true)->where('visible', true)->where('filtrable', true)
                    ->with(['valores' => fn ($query) => $query->where('activo', true)])
                    ->orderBy('orden')->orderBy('nombre')->get()
                : collect(),
        ];
    }

    /**
     * Calcula conteos disyuntivos sobre el conjunto completo, excluyendo la faceta propia.
     *
     * @param  array<string, mixed>  $filters
     * @return array{categories: Collection, brands: Collection, attributes: Collection}
     */
    public function facets(array $filters): array
    {
        $filtersWithoutCategory = $filters;
        unset($filtersWithoutCategory['categoria']);
        $filtersWithoutBrand = $filters;
        unset($filtersWithoutBrand['marca']);
        $categoryCounts = $this->query($filtersWithoutCategory)->without(['category', 'images'])->reorder()->select('categoria_id')
            ->selectRaw('COUNT(DISTINCT productos.id) AS total')->groupBy('categoria_id')->pluck('total', 'categoria_id');
        $brandCounts = $this->query($filtersWithoutBrand)->without(['category', 'images'])->reorder()->select('marca')
            ->selectRaw('COUNT(DISTINCT productos.id) AS total')->groupBy('marca')->pluck('total', 'marca');
        $attributeCounts = collect();

        if ($this->eavDisponible()) {
            $attributeCounts = $this->filterOptions()['attributes']->map(function (Atributo $atributo) use ($filters): array {
                $baseQuery = $this->query($filters, (int) $atributo->id)->select('productos.id');
                $values = DB::table('atributo_valores as valor')
                    ->join('producto_atributo_valor as pivote', 'pivote.atributo_valor_id', '=', 'valor.id')
                    ->where('valor.atributo_id', $atributo->id)->where('valor.activo', true)
                    ->whereIn('pivote.producto_id', $baseQuery)
                    ->select('valor.id', 'valor.atributo_id', 'valor.valor')
                    ->selectRaw('COUNT(DISTINCT pivote.producto_id) AS total')
                    ->groupBy('valor.id', 'valor.atributo_id', 'valor.valor')
                    ->orderBy('valor.orden')->orderBy('valor.valor')->get();

                return [
                    'id' => (int) $atributo->id,
                    'values' => $values->map(fn (object $value): array => [
                        'id' => (int) $value->id,
                        'label' => (string) $value->valor,
                        'count' => (int) $value->total,
                    ])->all(),
                ];
            })->filter(fn (array $attribute): bool => $attribute['values'] !== [])->values();
        }

        return ['categories' => $categoryCounts, 'brands' => $brandCounts, 'attributes' => $attributeCounts];
    }

    /**
     * Conserva únicamente las opciones que tienen coincidencias en el contexto actual.
     * Los valores ya seleccionados se conservan para que puedan desmarcarse.
     *
     * @param  array{categories: Collection, brands: Collection, attributes: Collection}  $options
     * @param  array{categories: Collection, brands: Collection, attributes: Collection}  $facets
     * @param  array<string, mixed>  $filters
     * @return array{categories: Collection, brands: Collection, attributes: Collection}
     */
    public function contextualizarOpciones(array $options, array $facets, array $filters): array
    {
        $selectedBrands = $this->listaDeValores($filters['marca'] ?? null);
        $brandCounts = $facets['brands'];
        $options['brands'] = $options['brands']->filter(
            fn (string $brand): bool => (int) $brandCounts->get($brand, 0) > 0 || in_array($brand, $selectedBrands, true),
        )->values();

        $selectedAttributes = collect($filters['atributos'] ?? [])->mapWithKeys(
            fn (mixed $values, int|string $attributeId): array => [
                (int) $attributeId => collect((array) $values)->map(fn (mixed $value): int => (int) $value)->all(),
            ],
        );
        $attributeCounts = collect($facets['attributes'])->keyBy('id');

        $options['attributes'] = $options['attributes']->map(function (Atributo $attribute) use ($attributeCounts, $selectedAttributes): Atributo {
            $countsByValue = collect($attributeCounts->get($attribute->id, [])['values'] ?? [])->keyBy('id');
            $selectedValues = collect($selectedAttributes->get($attribute->id, []));
            $values = $attribute->valores->filter(
                fn (object $value): bool => $countsByValue->has($value->id) || $selectedValues->contains((int) $value->id),
            )->values();
            $attribute->setRelation('valores', $values);

            return $attribute;
        })->filter(fn (Atributo $attribute): bool => $attribute->valores->isNotEmpty())->values();

        return $options;
    }

    private function eavDisponible(): bool
    {
        return Schema::hasTable('atributos') && Schema::hasTable('atributo_valores')
            && Schema::hasTable('producto_atributo_valor');
    }

    /** @return array<int, string> */
    private function listaDeValores(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return collect($values)->map(fn (mixed $item): string => trim((string) $item))
            ->filter()->unique()->take(20)->values()->all();
    }

    /** @param array<int|string, mixed> $attributes */
    private function aplicarFiltrosDeAtributos(Builder $query, array $attributes, ?int $excludedAttributeId): void
    {
        foreach ($attributes as $attributeId => $valueIds) {
            $attributeId = (int) $attributeId;
            if ($attributeId < 1 || $attributeId === $excludedAttributeId) {
                continue;
            }

            $values = collect(is_array($valueIds) ? $valueIds : [$valueIds])->map(fn (mixed $value): int => (int) $value)
                ->filter(fn (int $value): bool => $value > 0)->unique()->take(20)->values()->all();
            if ($values === []) {
                continue;
            }

            $query->whereExists(function (QueryBuilder $subquery) use ($attributeId, $values): void {
                $subquery->selectRaw('1')->from('producto_atributo_valor as pivote')
                    ->join('atributo_valores as valor', 'valor.id', '=', 'pivote.atributo_valor_id')
                    ->whereColumn('pivote.producto_id', 'productos.id')
                    ->where('valor.atributo_id', $attributeId)->whereIn('valor.id', $values)
                    ->where('valor.activo', true);
            });
        }
    }

    /** @return Builder<Producto> */
    public function conExistenciasPotenciales(Builder $productos): Builder
    {
        // Agrega a la consulta el stock potencial de las variantes.
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
        // Completa cada producto con stock visible segun el alcance del usuario.
        // Guarda en $ids el resultado de la consulta ejecutada.
        $ids = $productos->pluck('id')->all();
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($ids === []) {
            return;
        }

        // Guarda en $disponibles el resultado necesario para continuar la operacion.
        $disponibles = array_fill_keys($ids, 0);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($usuario !== null && (($usuario->can('gestion.cotizaciones.consultar') || $usuario->can('gestion.pedidos.consultar')) || $this->autorizacion->allows($usuario, 'gestion.pedidos.crear'))) {
            try {
                // Guarda en $disponibles el resultado necesario para continuar la operacion.
                $disponibles = array_replace($disponibles, $this->inventario->disponibilidadParaOperacion($usuario, $ids));
            } catch (HttpExceptionInterface) {
                // Guarda en $disponibles el resultado necesario para continuar la operacion.
                $disponibles = array_fill_keys($ids, 0);
            }
        } else {
            // Prepara en $serializados la consulta que obtiene o modifica los datos necesarios.
            $serializados = DB::table('variantes_productos')->whereIn('producto_id', $ids)
                ->where('controla_serie', true)->pluck('producto_id')
                ->merge(DB::table('dispositivos')->whereIn('producto_id', $ids)->pluck('producto_id'))
                ->map(fn (mixed $id): int => (int) $id)->unique()->all();
            // Prepara en $saldos la consulta que obtiene o modifica los datos necesarios.
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
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($saldos as $saldo) {
                // Guarda en $productoId el resultado necesario para continuar la operacion.
                $productoId = (int) $saldo->producto_id;
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ((int) $saldo->cantidad_saldos !== 1 || in_array($productoId, $serializados, true)) {
                    continue;
                }
                // Guarda en $unidades el resultado necesario para continuar la operacion.
                $unidades = (float) $saldo->unidades;
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($unidades >= 0 && $unidades === floor($unidades)) {
                    $disponibles[$productoId] = max($disponibles[$productoId], (int) $unidades);
                }
            }
        }

        $productos->each(fn (Producto $producto) => $producto->setAttribute('existencias_disponibles', $disponibles[$producto->id] ?? 0));
    }
}
