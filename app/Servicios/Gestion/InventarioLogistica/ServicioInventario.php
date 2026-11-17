<?php

namespace App\Servicios\Gestion\InventarioLogistica;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\MovimientoInventario;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Coordina disponibilidad, movimientos, ajustes y sincronización de existencias.
 */
class ServicioInventario
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $authorization,
        private readonly RegistradorAuditoria $audit,
    ) {}

    /** @return array{empresa_id:int,sede_id:?int} */
    public function alcanceConsulta(User $usuario, string $permiso = 'gestion.inventario.consultar'): array
    {
        $this->authorization->assertAllowed($usuario, $permiso);

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->authorization->allowsGlobal($usuario, $permiso)) {
            // Prepara en $empresas la consulta que obtiene o modifica los datos necesarios.
            // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
            $empresas = DB::table('empresas')->where('activo', true)->limit(2)->pluck('id');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($empresas->count() === 1, 403);

            return ['empresa_id' => (int) $empresas->first(), 'sede_id' => null];
        }

        // Guarda en $sedeId el resultado necesario para continuar la operacion.
        $sedeId = $this->authorization->activeSiteIdFor($usuario);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($sedeId !== null, 403);
        // Prepara en $sede la consulta que obtiene o modifica los datos necesarios.
        $sede = DB::table('sedes')->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->where('sedes.id', $sedeId)->where('sedes.activa', true)->where('empresas.activo', true)
            ->first(['sedes.empresa_id']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($sede !== null && $this->authorization->allowsRecord($usuario, $permiso, (int) $sede->empresa_id, $sedeId), 403);

        return ['empresa_id' => (int) $sede->empresa_id, 'sede_id' => $sedeId];
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Collection<int, object{id:int,nombre:string,sede_nombre:string}>
     */
    public function almacenesParaConsulta(array $alcance): Collection
    {
        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->where('sedes.empresa_id', $alcance['empresa_id'])
            ->where('empresas.activo', true)
            ->where('sedes.activa', true)
            ->where('almacenes.activo', true)
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('sedes.id', $alcance['sede_id']))
            ->orderBy('sedes.nombre')
            ->orderBy('almacenes.nombre')
            ->get(['almacenes.id', 'almacenes.nombre', 'sedes.nombre AS sede_nombre']);
    }

    public function disponibilidadDeLinea(User $usuario, Producto $producto, ?int $varianteId = null): int
    {
        // Guarda en $ubicacion el resultado necesario para continuar la operacion.
        $ubicacion = $this->authorization->operationLocation($usuario);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 403);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless((int) $producto->empresa_id === (int) $ubicacion['empresa_id'], 403);

        // Prepara en $consulta la consulta que obtiene o modifica los datos necesarios.
        $consulta = DB::table('existencias_almacen')
            ->where('empresa_id', $ubicacion['empresa_id'])
            ->where('sede_id', $ubicacion['sede_id'])
            ->where('almacen_id', $ubicacion['almacen_id'])
            ->where('producto_id', $producto->id);

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($varianteId === null) {
            $consulta->whereNull('variante_id');
        } else {
            $consulta->where('variante_id', $varianteId);
        }

        // Guarda en $saldo el resultado de la consulta ejecutada.
        $saldo = $consulta->first(['cantidad_fisica', 'cantidad_reservada']);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($saldo === null) {
            return 0;
        }

        return max(0, (int) floor((float) $saldo->cantidad_fisica - (float) $saldo->cantidad_reservada));
    }

    /**
     * Resuelve disponibilidad para visitantes usando la única ubicación pública activa.
     */
    public function disponibilidadPublica(Producto $producto, ?int $varianteId = null): int
    {
        // Guarda en $location el resultado necesario para continuar la operacion.
        $location = $this->ubicacionPublica();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless((int) $producto->empresa_id === $location['empresa_id'], 404);
        // Prepara en $query la consulta que obtiene o modifica los datos necesarios.
        $query = DB::table('existencias_almacen')->where($location)->where('producto_id', $producto->id);
        $varianteId === null ? $query->whereNull('variante_id') : $query->where('variante_id', $varianteId);
        // Guarda en $saldo el resultado de la consulta ejecutada.
        $saldo = $query->first(['cantidad_fisica', 'cantidad_reservada']);

        return $saldo === null ? 0 : max(0, (int) floor((float) $saldo->cantidad_fisica - (float) $saldo->cantidad_reservada));
    }

    /** @return array{empresa_id:int,sede_id:int,almacen_id:int} */
    public function ubicacionPublica(): array
    {
        // Prepara en $company la consulta que obtiene o modifica los datos necesarios.
        $company = DB::table('empresas')->where('activo', true)->orderBy('id')->first(['id']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($company !== null, 503, 'La tienda no tiene una empresa activa configurada.');
        // Prepara en $site la consulta que obtiene o modifica los datos necesarios.
        $site = DB::table('sedes')->where('empresa_id', $company->id)->where('activa', true)->orderBy('id')->first(['id']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($site !== null, 503, 'La tienda no tiene una sede activa configurada.');
        // Prepara en $warehouse la consulta que obtiene o modifica los datos necesarios.
        $warehouse = DB::table('almacenes')->where('sede_id', $site->id)->where('activo', true)->orderBy('id')->first(['id']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($warehouse !== null, 503, 'La tienda no tiene un almacén activo configurado.');

        return ['empresa_id' => (int) $company->id, 'sede_id' => (int) $site->id, 'almacen_id' => (int) $warehouse->id];
    }

    public function cambiarProductoBloqueadoEnAlmacenPublico(
        Producto $product,
        ?int $variantId,
        string $movementType,
        int $quantity,
        string $notes,
        ?string $documentType = null,
        ?string $documentId = null,
    ): ?MovimientoInventario {
        // Guarda en $location el resultado necesario para continuar la operacion.
        $location = $this->ubicacionPublica();
        // Prepara en $balanceQuery la consulta que obtiene o modifica los datos necesarios.
        $balanceQuery = DB::table('existencias_almacen')->where($location)->where('producto_id', $product->id);
        $variantId === null ? $balanceQuery->whereNull('variante_id') : $balanceQuery->where('variante_id', $variantId);
        // Guarda en $balance el resultado de la consulta ejecutada.
        $balance = $balanceQuery->lockForUpdate()->first(['id', 'cantidad_fisica', 'cantidad_reservada']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($balance !== null, 409, 'No existe saldo para el producto o variante.');
        // Guarda en $next el resultado necesario para continuar la operacion.
        $next = (int) $balance->cantidad_fisica - $quantity;
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($movementType === 'salida' && $quantity > 0 && $next >= 0, 422, 'La salida supera el stock disponible.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if((float) $balance->cantidad_reservada > $next, 422, 'El movimiento dejaría reservas sin respaldo.');
        DB::table('existencias_almacen')->where('id', $balance->id)->update(['cantidad_fisica' => $next]);
        // Guarda en $total el resultado necesario para continuar la operacion.
        $total = (int) DB::table('existencias_almacen')->where('producto_id', $product->id)->where('empresa_id', $location['empresa_id'])->sum('cantidad_fisica');
        $product->update(['existencias' => $total]);

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return MovimientoInventario::query()->create([
            'producto_id' => $product->id, 'usuario_id' => null, 'tipo_movimiento' => 'salida', 'cantidad' => $quantity,
            'notas' => $notes, 'creado_en' => now(), ...$location, 'variante_id' => $variantId,
            'documento_tipo' => $documentType, 'documento_id' => $documentId,
        ]);
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @param  array{buscar?:?string,categoria?:?int,estado?:?string,proveedor?:?int,stock?:?string,oferta?:bool}  $filtros
     * @return Builder<Producto>
     */
    public function productosParaConsulta(
        array $alcance,
        array $filtros = [],
        bool $incluirSinExistencias = false,
        bool $incluirInactivos = false,
    ): Builder {
        // Prepara en $saldos la consulta que obtiene o modifica los datos necesarios.
        $saldos = DB::table('existencias_almacen as saldo')
            ->select('saldo.producto_id')
            ->selectRaw('SUM(saldo.cantidad_fisica) AS cantidad_fisica')
            ->selectRaw('SUM(saldo.cantidad_reservada) AS cantidad_reservada')
            ->where('saldo.empresa_id', $alcance['empresa_id'])
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('saldo.sede_id', $alcance['sede_id']))
            ->groupBy('saldo.producto_id');

        // Prepara en $consulta la consulta que obtiene o modifica los datos necesarios.
        $consulta = Producto::query()
            ->leftJoinSub($saldos, 'saldo_producto', fn ($union) => $union->on('saldo_producto.producto_id', '=', 'productos.id'))
            ->select('productos.*')
            ->selectRaw('COALESCE(saldo_producto.cantidad_fisica, 0) AS existencias_alcance')
            ->selectRaw('COALESCE(saldo_producto.cantidad_reservada, 0) AS existencias_reservadas')
            ->where('productos.empresa_id', $alcance['empresa_id']);

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $incluirInactivos) {
            $consulta->where('productos.activo', true);
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $incluirSinExistencias) {
            $consulta->whereNotNull('saldo_producto.producto_id');
        }

        // Guarda en $buscar el resultado necesario para continuar la operacion.
        $buscar = $filtros['buscar'] ?? null;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($buscar !== null) {
            $consulta->where(function (Builder $busqueda) use ($buscar): void {
                $busqueda->where('productos.nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('productos.marca', 'like', '%'.$buscar.'%')
                    ->orWhere('productos.etiqueta', 'like', '%'.$buscar.'%')
                    ->orWhere('productos.categoria', 'like', '%'.$buscar.'%')
                    ->orWhereHas('variants', fn (Builder $variantes): Builder => $variantes
                        ->where('codigo_sku', 'like', '%'.$buscar.'%')
                        ->orWhere('codigo_barras', 'like', '%'.$buscar.'%'));
            });
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (($filtros['categoria'] ?? null) !== null) {
            $consulta->where('productos.categoria_id', $filtros['categoria']);
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (($filtros['proveedor'] ?? null) !== null) {
            $consulta->whereHas('suppliers', fn (Builder $proveedores): Builder => $proveedores->whereKey($filtros['proveedor']));
        }

        // Guarda en $estado el resultado necesario para continuar la operacion.
        $estado = $filtros['estado'] ?? null;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($estado === 'activo') {
            $consulta->where('productos.activo', true);
        } elseif ($estado === 'inactivo') {
            $consulta->where('productos.activo', false);
        } elseif ($estado === 'con_existencias') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) > ?', [0]);
        } elseif ($estado === 'sin_existencias') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) <= ?', [0]);
        }

        // Guarda en $stock el resultado necesario para continuar la operacion.
        $stock = $filtros['stock'] ?? null;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($stock === 'sin_stock') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) <= ?', [0]);
        } elseif ($stock === 'con_stock') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) > ?', [0]);
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($filtros['oferta'] ?? false) {
            $consulta->whereNotNull('productos.precio_oferta')
                ->whereColumn('productos.precio_oferta', '<', 'productos.precio');
        }

        return $consulta;
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return array{productos_activos:int,productos_con_existencias:int,productos_sin_existencias:int,valor_inventario:float,productos_en_oferta:int}
     */
    public function resumenProductos(array $alcance): array
    {
        // Prepara en $saldos la consulta que obtiene o modifica los datos necesarios.
        $saldos = DB::table('existencias_almacen')->select('producto_id')
            ->selectRaw('SUM(cantidad_fisica) AS unidades')
            ->where('empresa_id', $alcance['empresa_id'])
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('sede_id', $alcance['sede_id']))
            ->groupBy('producto_id');

        // Prepara en $resumen la consulta que obtiene o modifica los datos necesarios.
        $resumen = Producto::query()
            ->leftJoinSub($saldos, 'saldos', fn ($union) => $union->on('saldos.producto_id', '=', 'productos.id'))
            ->where('productos.empresa_id', $alcance['empresa_id'])
            ->selectRaw('COUNT(CASE WHEN productos.activo = 1 THEN 1 END) AS productos_activos')
            ->selectRaw('COUNT(CASE WHEN productos.activo = 1 AND COALESCE(saldos.unidades, 0) > 0 THEN 1 END) AS productos_con_existencias')
            ->selectRaw('COUNT(CASE WHEN productos.activo = 1 AND COALESCE(saldos.unidades, 0) <= 0 THEN 1 END) AS productos_sin_existencias')
            ->selectRaw('COALESCE(SUM(CASE WHEN productos.activo = 1 THEN COALESCE(saldos.unidades, 0) * COALESCE(productos.precio_oferta, productos.precio) ELSE 0 END), 0) AS valor_inventario')
            ->selectRaw('COUNT(CASE WHEN productos.activo = 1 AND productos.precio_oferta IS NOT NULL AND productos.precio_oferta < productos.precio THEN 1 END) AS productos_en_oferta')
            ->first();

        return [
            'productos_activos' => (int) $resumen->productos_activos,
            'productos_con_existencias' => (int) $resumen->productos_con_existencias,
            'productos_sin_existencias' => (int) $resumen->productos_sin_existencias,
            'valor_inventario' => (float) $resumen->valor_inventario,
            'productos_en_oferta' => (int) $resumen->productos_en_oferta,
        ];
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @param  array<int, int>  $productos
     * @return Collection<int, object{variante_id:int,cantidad_fisica:string,cantidad_reservada:string}>
     */
    public function saldosPorVariante(array $alcance, array $productos): Collection
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($productos === []) {
            return collect();
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return DB::table('existencias_almacen')
            ->where('empresa_id', $alcance['empresa_id'])
            ->whereIn('producto_id', $productos)
            ->whereNotNull('variante_id')
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('sede_id', $alcance['sede_id']))
            ->select('variante_id')
            ->selectRaw('SUM(cantidad_fisica) AS cantidad_fisica')
            ->selectRaw('SUM(cantidad_reservada) AS cantidad_reservada')
            ->groupBy('variante_id')
            ->get()
            ->keyBy('variante_id');
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return array{productos:int,sin_existencias:int,valor_catalogo:float}
     */
    public function resumenConsulta(array $alcance): array
    {
        // Prepara en $saldos la consulta que obtiene o modifica los datos necesarios.
        $saldos = DB::table('existencias_almacen')->select('producto_id')
            ->selectRaw('SUM(cantidad_fisica) AS unidades')
            ->where('empresa_id', $alcance['empresa_id'])
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('sede_id', $alcance['sede_id']))
            ->groupBy('producto_id');
        // Prepara en $resumen la consulta que obtiene o modifica los datos necesarios.
        $resumen = Producto::query()
            ->leftJoinSub($saldos, 'saldos', fn ($union) => $union->on('saldos.producto_id', '=', 'productos.id'))
            ->where('productos.empresa_id', $alcance['empresa_id'])
            ->where('productos.activo', true)
            ->selectRaw('COUNT(CASE WHEN COALESCE(saldos.unidades, 0) > 0 THEN 1 END) AS productos')
            ->selectRaw('COUNT(CASE WHEN COALESCE(saldos.unidades, 0) <= 0 THEN 1 END) AS sin_existencias')
            ->selectRaw('COALESCE(SUM(COALESCE(saldos.unidades, 0) * productos.precio), 0) AS valor_catalogo')
            ->first();

        return [
            'productos' => (int) $resumen->productos,
            'sin_existencias' => (int) $resumen->sin_existencias,
            'valor_catalogo' => (float) $resumen->valor_catalogo,
        ];
    }

    /**
     * Calcula unidades disponibles para vender en la ubicación autorizada del usuario.
     *
     * Filtra identificadores positivos, verifica almacén activo y excluye productos o
     * variantes serializadas; el saldo final es físico menos reservado. No modifica datos.
     *
     * @param  User  $usuario  Usuario cuya empresa, sede y almacén delimitan la consulta.
     * @param  array<int, int|string>  $identificadores  Identificadores de productos.
     * @return array<int, int> Existencias disponibles indexadas por producto.
     *
     * @throws HttpException Si no existe ubicación autorizada.
     */
    public function disponibilidadParaOperacion(User $usuario, array $identificadores): array
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $usuario->can('gestion.cotizaciones.consultar') && ! $usuario->can('gestion.pedidos.consultar')) {
            $this->authorization->assertAllowed($usuario, 'gestion.pedidos.crear');
        }
        // Guarda en $ubicacion el resultado necesario para continuar la operacion.
        $ubicacion = $this->authorization->operationLocation($usuario);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
        $ids = collect($identificadores)->map(fn (int|string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)->unique()->values()->all();
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($ids === []) {
            return [];
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! DB::table('almacenes')->where('id', $ubicacion['almacen_id'])
            ->where('activo', true)->where('permite_venta', true)->exists()) {
            return array_fill_keys($ids, 0);
        }

        // Prepara en $productos la consulta que obtiene o modifica los datos necesarios.
        $productos = Producto::query()->whereIn('id', $ids)->where('empresa_id', $ubicacion['empresa_id'])
            ->where('activo', true)->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        // Prepara en $saldos la consulta que obtiene o modifica los datos necesarios.
        $saldos = DB::table('existencias_almacen')->where('empresa_id', $ubicacion['empresa_id'])
            ->where('sede_id', $ubicacion['sede_id'])->where('almacen_id', $ubicacion['almacen_id'])
            ->whereIn('producto_id', $productos)->get(['producto_id', 'variante_id', 'cantidad_fisica', 'cantidad_reservada'])
            ->groupBy('producto_id');
        // Guarda en $variantes el resultado de la consulta ejecutada.
        $variantes = $saldos->flatten(1)->pluck('variante_id')->filter()->unique()->values()->all();
        $variantesSerializadas = $variantes !== [] && Schema::hasTable('variantes_productos') && Schema::hasColumn('variantes_productos', 'controla_serie')
            ? DB::table('variantes_productos')->whereIn('id', $variantes)->where('controla_serie', true)->pluck('id')->map(fn (mixed $id): int => (int) $id)->all()
            : $variantes;
        // Guarda en $productosSerializados el resultado necesario para continuar la operacion.
        $productosSerializados = Schema::hasTable('dispositivos')
            ? DB::table('dispositivos')->whereIn('producto_id', $productos)->distinct()->pluck('producto_id')->map(fn (mixed $id): int => (int) $id)->all()
            : [];

        // Agrupa en $disponibilidad los datos que se utilizaran en el siguiente paso.
        $disponibilidad = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($productos as $productoId) {
            // Guarda en $registros el resultado de la consulta ejecutada.
            $registros = $saldos->get($productoId, collect());
            $saldo = $registros->count() === 1 ? $registros->first() : null;
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($saldo === null || in_array($productoId, $productosSerializados, true)
                || ($saldo->variante_id !== null && in_array((int) $saldo->variante_id, $variantesSerializadas, true))) {
                $disponibilidad[$productoId] = 0;

                continue;
            }
            // Guarda en $fisica el resultado necesario para continuar la operacion.
            $fisica = (float) $saldo->cantidad_fisica;
            // Guarda en $reservada el resultado necesario para continuar la operacion.
            $reservada = (float) $saldo->cantidad_reservada;
            $disponibilidad[$productoId] = $fisica >= 0 && $fisica === floor($fisica) && $reservada >= 0
                ? max(0, (int) floor($fisica - $reservada)) : 0;
        }

        return $disponibilidad;
    }

    /**
     * Crea el saldo inicial en almacén para un producto recién registrado.
     *
     * Exige una transacción abierta, verifica la ubicación autorizada, fija existencias
     * del producto en cero e inserta su saldo físico y reservado inicial.
     *
     * @param  Producto  $product  Producto cuyo saldo se inicializa.
     * @param  User  $user  Usuario que determina la ubicación autorizada.
     * @return void Persiste el saldo inicial sin devolver datos.
     *
     * @throws HttpException Si falta la transacción, el esquema o el alcance.
     */
    public function registrarProductoNuevo(Producto $product, User $user): void
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(DB::transactionLevel() > 0 && Schema::hasTable('existencias_almacen'), 503, 'El inventario requiere una transacción y un almacén configurado.');
        // Guarda en $location el resultado necesario para continuar la operacion.
        $location = $this->authorization->operationLocation($user);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null && $location['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless((int) $product->empresa_id === $location['empresa_id'], 403);

        $product->update(['existencias' => 0]);

        DB::table('existencias_almacen')->insert([
            'almacen_id' => $location['almacen_id'],
            'producto_id' => $product->id,
            'variante_id' => null,
            'empresa_id' => $location['empresa_id'],
            'sede_id' => $location['sede_id'],
            'cantidad_fisica' => 0,
            'cantidad_reservada' => 0,
        ]);
    }

    /**
     * Ajusta inventario bajo bloqueo y registra el movimiento de auditoría.
     *
     * Autoriza la acción, abre una transacción, bloquea el producto, delega el cambio
     * al método de existencias bloqueadas y registra el movimiento resultante.
     *
     * @param  Producto  $product  Producto cuyo saldo se ajusta.
     * @param  string  $movementType  Tipo de movimiento.
     * @param  int  $quantity  Cantidad del ajuste.
     * @param  string  $notes  Motivo guardado con el movimiento.
     * @param  User  $user  Usuario que ejecuta el ajuste.
     * @return ?MovimientoInventario Movimiento creado o null cuando no se produce movimiento.
     *
     * @throws HttpException Si falla autorización o validación.
     */
    public function ajustar(Producto $product, string $movementType, int $quantity, string $notes, User $user): ?MovimientoInventario
    {
        $this->authorization->assertAllowed($user, 'gestion.inventario.ajustar');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($product, $movementType, $quantity, $notes, $user): ?MovimientoInventario {
            // Guarda en $lockedProduct el resultado necesario para continuar la operacion.
            $lockedProduct = Producto::whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            // Guarda en $movement el resultado necesario para continuar la operacion.
            $movement = $this->cambiarProductoBloqueado($lockedProduct, $movementType, $quantity, $notes, $user);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($movement === null) {
                return null;
            }

            $this->audit->record($user->id, 'inventory.adjusted', 'movimientos_inventario', $movement->id, null, [
                'producto_id' => $product->id,
                'tipo_movimiento' => $movementType,
                'cantidad' => $movement->cantidad,
            ], request()->ip());

            return $movement;
        }, 3);
    }

    /**
     * Registra un movimiento manual de inventario en un almacén autorizado.
     *
     * Valida empresa, sede, almacén, variante y tipo de movimiento; bloquea el
     * producto y delega el cambio a la rutina transaccional de existencias.
     *
     * @param  Producto  $producto  Producto cuyo stock se modifica.
     * @param  int|null  $varianteId  Variante opcional del producto.
     * @param  int  $almacenId  Almacén destino.
     * @param  string  $tipoMovimiento  Entrada, salida o ajuste.
     * @param  int  $cantidad  Cantidad solicitada.
     * @param  string  $notas  Observación del movimiento.
     * @param  User  $usuario  Usuario autorizado.
     * @return MovimientoInventario|null Movimiento creado o null si el ajuste no cambia stock.
     */
    public function registrarMovimientoEnAlmacen(
        Producto $producto,
        ?int $varianteId,
        int $almacenId,
        string $tipoMovimiento,
        int $cantidad,
        string $notas,
        User $usuario,
    ): ?MovimientoInventario {
        $this->authorization->assertAllowed($usuario, 'gestion.inventario.ajustar');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($producto, $varianteId, $almacenId, $tipoMovimiento, $cantidad, $notas, $usuario): ?MovimientoInventario {
            // Prepara en $ubicacion la consulta que obtiene o modifica los datos necesarios.
            $ubicacion = DB::table('almacenes')
                ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
                ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
                ->where('almacenes.id', $almacenId)
                ->where('almacenes.activo', true)
                ->where('sedes.activa', true)
                ->where('empresas.activo', true)
                ->first(['empresas.id AS empresa_id', 'sedes.id AS sede_id', 'almacenes.id AS almacen_id']);

            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($ubicacion !== null && (int) $producto->empresa_id === (int) $ubicacion->empresa_id, 403);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->authorization->allowsRecord(
                $usuario,
                'gestion.inventario.ajustar',
                (int) $ubicacion->empresa_id,
                (int) $ubicacion->sede_id,
            ), 403);

            // Prepara en $productoBloqueado la consulta que obtiene o modifica los datos necesarios.
            $productoBloqueado = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $tieneVariantes la consulta que obtiene o modifica los datos necesarios.
            $tieneVariantes = DB::table('variantes_productos')->where('producto_id', $productoBloqueado->id)->exists();
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($varianteId === null) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($tieneVariantes, 422, 'Selecciona la variante del producto antes de registrar el movimiento.');
            } else {
                // Prepara en $variante la consulta que obtiene o modifica los datos necesarios.
                $variante = DB::table('variantes_productos')
                    ->where('id', $varianteId)
                    ->where('producto_id', $productoBloqueado->id)
                    ->first(['estado']);

                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($variante !== null, 422, 'La variante no pertenece al producto seleccionado.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($tipoMovimiento === 'entrada' && $variante->estado !== 'activo', 422, 'No se pueden ingresar existencias a una variante inactiva.');
            }

            // Guarda en $movimiento el resultado necesario para continuar la operacion.
            $movimiento = $this->cambiarProductoBloqueadoEnAlmacen(
                $productoBloqueado,
                $varianteId,
                $tipoMovimiento,
                $cantidad,
                $notas,
                $usuario,
                (int) $ubicacion->empresa_id,
                (int) $ubicacion->sede_id,
                (int) $ubicacion->almacen_id,
                'ajuste_manual',
                null,
                true,
            );

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($movimiento === null) {
                return null;
            }

            $this->audit->record($usuario->id, 'inventory.adjusted', 'movimientos_inventario', $movimiento->id, null, [
                'producto_id' => $productoBloqueado->id,
                'variante_id' => $varianteId,
                'almacen_id' => $almacenId,
                'tipo_movimiento' => $tipoMovimiento,
                'cantidad' => $movimiento->cantidad,
            ], request()->ip());

            return $movimiento;
        }, 3);
    }

    /**
     * Cambia existencias de un producto ya bloqueado dentro de una transacción.
     *
     * Valida cantidades, ubicación RBAC, reservas y consistencia de balances;
     * actualiza existencias heredadas y crea el movimiento cuando hay diferencia.
     *
     * @param  Producto  $product  Producto bloqueado.
     * @param  string  $movementType  Entrada, salida o ajuste.
     * @param  int  $quantity  Cantidad nueva o desplazamiento solicitado.
     * @param  string  $notes  Observación del movimiento.
     * @param  User  $user  Usuario autorizado.
     * @return MovimientoInventario|null Movimiento creado o null si no hubo cambio.
     */
    public function cambiarProductoBloqueado(Producto $product, string $movementType, int $quantity, string $notes, User $user): ?MovimientoInventario
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(DB::transactionLevel() > 0, 503, 'El movimiento de inventario requiere una transacción.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($quantity < 0 || ($movementType !== 'ajuste' && $quantity === 0), 422, 'La cantidad del movimiento no es válida.');
        // Guarda en $location el resultado necesario para continuar la operacion.
        $location = $this->authorization->operationLocation($user);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null && $location['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless((int) $product->empresa_id === $location['empresa_id'], 403);

        // Prepara en $balances la consulta que obtiene o modifica los datos necesarios.
        $balances = DB::table('existencias_almacen')
            ->where('empresa_id', $location['empresa_id'])
            ->where('sede_id', $location['sede_id'])
            ->where('almacen_id', $location['almacen_id'])
            ->where('producto_id', $product->id)
            ->lockForUpdate()
            ->get(['id', 'variante_id', 'cantidad_fisica', 'cantidad_reservada']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($balances->count() === 1, 409, 'El producto requiere conciliar o seleccionar su existencia por variante.');
        // Guarda en $balance el resultado de la consulta ejecutada.
        $balance = $balances->first();
        // Prepara en $productBalances la consulta que obtiene o modifica los datos necesarios.
        $productBalances = DB::table('existencias_almacen')->where('producto_id', $product->id)->where('empresa_id', $location['empresa_id']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if((clone $productBalances)->whereNull('variante_id')->exists() && (clone $productBalances)->whereNotNull('variante_id')->exists(), 409, 'Las existencias por producto y variante requieren conciliación.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if((float) $balance->cantidad_fisica !== floor((float) $balance->cantidad_fisica), 422, 'El formulario no admite existencias fraccionarias.');
        // Guarda en $previousStock el resultado necesario para continuar la operacion.
        $previousStock = (int) $balance->cantidad_fisica;
        // Guarda en $nextStock el resultado necesario para continuar la operacion.
        $nextStock = match ($movementType) {
            'entrada' => $previousStock + $quantity,
            'salida' => $previousStock - $quantity,
            'ajuste' => $quantity,
            default => throw new \InvalidArgumentException('Tipo de movimiento inválido.'),
        };

        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($nextStock < 0, 422, 'La salida supera el stock disponible.');

        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if((float) $balance->cantidad_reservada > $nextStock, 422, 'El movimiento dejaría reservas sin respaldo.');
        $variantId = $balance->variante_id === null ? null : (int) $balance->variante_id;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($movementType === 'ajuste' && $nextStock === $previousStock) {
            return null;
        }

        DB::table('existencias_almacen')->where('id', $balance->id)->update(['cantidad_fisica' => $nextStock]);

        // Guarda en $total el resultado necesario para continuar la operacion.
        $total = (float) DB::table('existencias_almacen')->where('producto_id', $product->id)->where('empresa_id', $location['empresa_id'])->sum('cantidad_fisica');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($total !== floor($total) || $total > 4294967295, 422, 'No es posible actualizar la proyección heredada de existencias.');
        $product->update(['existencias' => (int) $total]);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($variantId !== null) {
            // Guarda en $variantTotal el resultado necesario para continuar la operacion.
            $variantTotal = (float) DB::table('existencias_almacen')->where('variante_id', $variantId)->sum('cantidad_fisica');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($variantTotal !== floor($variantTotal) || $variantTotal > 4294967295, 422, 'No es posible actualizar la proyección heredada de la variante.');
            DB::table('variantes_productos')->where('id', $variantId)->update(['existencias' => (int) $variantTotal]);
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return MovimientoInventario::create([
            'producto_id' => $product->id,
            'usuario_id' => $user->id,
            'tipo_movimiento' => $movementType,
            'cantidad' => $movementType === 'ajuste' ? $nextStock - $previousStock : $quantity,
            'notas' => $notes,
            'creado_en' => now(),
            'variante_id' => $variantId,
            ...$location,
        ]);
    }

    /**
     * Ajusta el balance de una ubicación concreta y sincroniza las existencias heredadas.
     *
     * Requiere una transacción activa, valida pertenencia del almacén, bloquea el
     * balance por producto/variante y puede crear el balance faltante solo cuando
     * la llamada lo solicita para un ajuste.
     *
     * @param  Producto  $product  Producto bloqueado.
     * @param  int|null  $variantId  Variante opcional.
     * @param  string  $movementType  Tipo de movimiento.
     * @param  int  $quantity  Cantidad solicitada.
     * @param  string  $notes  Observación.
     * @param  User  $user  Usuario que origina el movimiento.
     * @param  int  $companyId  Empresa autorizada.
     * @param  int  $siteId  Sede autorizada.
     * @param  int  $warehouseId  Almacén autorizado.
     * @param  string|null  $documentType  Tipo de documento origen.
     * @param  string|null  $documentId  Identificador del documento origen.
     * @param  bool  $createMissingBalance  Indica si puede crear balance inexistente.
     * @return MovimientoInventario|null Movimiento creado o null cuando no hay cambio.
     */
    public function cambiarProductoBloqueadoEnAlmacen(
        Producto $product,
        ?int $variantId,
        string $movementType,
        int $quantity,
        string $notes,
        User $user,
        int $companyId,
        int $siteId,
        int $warehouseId,
        ?string $documentType = null,
        ?string $documentId = null,
        bool $createMissingBalance = false,
    ): ?MovimientoInventario {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(DB::transactionLevel() > 0, 503, 'El movimiento de inventario requiere una transaccion.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($quantity < 0 || ($movementType !== 'ajuste' && $quantity === 0), 422, 'La cantidad del movimiento no es valida.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless((int) $product->empresa_id === $companyId, 403);
        // Prepara en $warehouseIsValid la consulta que obtiene o modifica los datos necesarios.
        $warehouseIsValid = DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('almacenes.id', $warehouseId)
            ->where('almacenes.sede_id', $siteId)
            ->where('sedes.empresa_id', $companyId)
            ->where('almacenes.activo', true)
            ->where('sedes.activa', true)
            ->exists();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($warehouseIsValid, 422, 'El almacen no pertenece a una sede activa de la empresa.');

        // Prepara en $balanceQuery la consulta que obtiene o modifica los datos necesarios.
        $balanceQuery = DB::table('existencias_almacen')
            ->where('empresa_id', $companyId)
            ->where('sede_id', $siteId)
            ->where('almacen_id', $warehouseId)
            ->where('producto_id', $product->id);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($variantId === null) {
            $balanceQuery->whereNull('variante_id');
        } else {
            $balanceQuery->where('variante_id', $variantId);
        }
        // Guarda en $balance el resultado de la consulta ejecutada.
        $balance = $balanceQuery->lockForUpdate()->first(['id', 'cantidad_fisica', 'cantidad_reservada']);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($balance === null && $createMissingBalance && $movementType === 'ajuste' && $quantity === 0) {
            return null;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($balance === null && $createMissingBalance) {
            DB::table('existencias_almacen')->insertOrIgnore([
                'almacen_id' => $warehouseId, 'variante_id' => $variantId, 'producto_id' => $product->id,
                'empresa_id' => $companyId, 'sede_id' => $siteId, 'cantidad_fisica' => 0, 'cantidad_reservada' => 0,
            ]);
            // Prepara en $balanceQuery la consulta que obtiene o modifica los datos necesarios.
            $balanceQuery = DB::table('existencias_almacen')->where('empresa_id', $companyId)
                ->where('sede_id', $siteId)->where('almacen_id', $warehouseId)->where('producto_id', $product->id);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($variantId === null) {
                $balanceQuery->whereNull('variante_id');
            } else {
                $balanceQuery->where('variante_id', $variantId);
            }
            // Guarda en $balance el resultado de la consulta ejecutada.
            $balance = $balanceQuery->lockForUpdate()->first(['id', 'cantidad_fisica', 'cantidad_reservada']);
        }
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($balance !== null, 409, 'No existe saldo para el producto o variante en el almacen.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if((float) $balance->cantidad_fisica !== floor((float) $balance->cantidad_fisica), 422, 'El formulario no admite existencias fraccionarias.');

        // Guarda en $previousStock el resultado necesario para continuar la operacion.
        $previousStock = (int) $balance->cantidad_fisica;
        // Guarda en $nextStock el resultado necesario para continuar la operacion.
        $nextStock = match ($movementType) {
            'entrada' => $previousStock + $quantity,
            'salida' => $previousStock - $quantity,
            'ajuste' => $quantity,
            default => throw new \InvalidArgumentException('Tipo de movimiento invalido.'),
        };
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($nextStock < 0, 422, 'La salida supera el stock disponible.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if((float) $balance->cantidad_reservada > $nextStock, 422, 'El movimiento dejaria reservas sin respaldo.');
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($movementType === 'ajuste' && $nextStock === $previousStock) {
            return null;
        }

        DB::table('existencias_almacen')->where('id', $balance->id)->update(['cantidad_fisica' => $nextStock]);
        // Guarda en $total el resultado necesario para continuar la operacion.
        $total = (float) DB::table('existencias_almacen')->where('producto_id', $product->id)->where('empresa_id', $companyId)->sum('cantidad_fisica');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($total !== floor($total) || $total > 4294967295, 422, 'No es posible actualizar la proyeccion heredada de existencias.');
        $product->update(['existencias' => (int) $total]);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($variantId !== null) {
            // Guarda en $variantTotal el resultado necesario para continuar la operacion.
            $variantTotal = (float) DB::table('existencias_almacen')->where('variante_id', $variantId)->sum('cantidad_fisica');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($variantTotal !== floor($variantTotal) || $variantTotal > 4294967295, 422, 'No es posible actualizar la proyeccion heredada de la variante.');
            DB::table('variantes_productos')->where('id', $variantId)->update(['existencias' => (int) $variantTotal]);
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return MovimientoInventario::query()->create([
            'producto_id' => $product->id,
            'usuario_id' => $user->id,
            'tipo_movimiento' => $movementType,
            'cantidad' => $movementType === 'ajuste' ? $nextStock - $previousStock : $quantity,
            'notas' => $notes,
            'creado_en' => now(),
            'empresa_id' => $companyId,
            'sede_id' => $siteId,
            'almacen_id' => $warehouseId,
            'variante_id' => $variantId,
            'documento_tipo' => $documentType,
            'documento_id' => $documentId,
        ]);
    }
}
