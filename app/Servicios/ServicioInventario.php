<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\MovimientoInventario;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioInventario dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
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

        if ($this->authorization->allowsGlobal($usuario, $permiso)) {
            $empresas = DB::table('empresas')->where('activo', true)->limit(2)->pluck('id');
            abort_unless($empresas->count() === 1, 403);

            return ['empresa_id' => (int) $empresas->first(), 'sede_id' => null];
        }

        $sedeId = $this->authorization->activeSiteIdFor($usuario);
        abort_unless($sedeId !== null, 403);
        $sede = DB::table('sedes')->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->where('sedes.id', $sedeId)->where('sedes.activa', true)->where('empresas.activo', true)
            ->first(['sedes.empresa_id']);
        abort_unless($sede !== null && $this->authorization->allowsRecord($usuario, $permiso, (int) $sede->empresa_id, $sedeId), 403);

        return ['empresa_id' => (int) $sede->empresa_id, 'sede_id' => $sedeId];
    }

    /**
     * @param  array{empresa_id:int,sede_id:?int}  $alcance
     * @return Collection<int, object{id:int,nombre:string,sede_nombre:string}>
     */
    public function almacenesParaConsulta(array $alcance): Collection
    {
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
        $ubicacion = $this->authorization->operationLocation($usuario);
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 403);
        abort_unless((int) $producto->empresa_id === (int) $ubicacion['empresa_id'], 403);

        $consulta = DB::table('existencias_almacen')
            ->where('empresa_id', $ubicacion['empresa_id'])
            ->where('sede_id', $ubicacion['sede_id'])
            ->where('almacen_id', $ubicacion['almacen_id'])
            ->where('producto_id', $producto->id);

        if ($varianteId === null) {
            $consulta->whereNull('variante_id');
        } else {
            $consulta->where('variante_id', $varianteId);
        }

        $saldo = $consulta->first(['cantidad_fisica', 'cantidad_reservada']);
        if ($saldo === null) {
            return 0;
        }

        return max(0, (int) floor((float) $saldo->cantidad_fisica - (float) $saldo->cantidad_reservada));
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
        $saldos = DB::table('existencias_almacen as saldo')
            ->select('saldo.producto_id')
            ->selectRaw('SUM(saldo.cantidad_fisica) AS cantidad_fisica')
            ->selectRaw('SUM(saldo.cantidad_reservada) AS cantidad_reservada')
            ->where('saldo.empresa_id', $alcance['empresa_id'])
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('saldo.sede_id', $alcance['sede_id']))
            ->groupBy('saldo.producto_id');

        $consulta = Producto::query()
            ->leftJoinSub($saldos, 'saldo_producto', fn ($union) => $union->on('saldo_producto.producto_id', '=', 'productos.id'))
            ->select('productos.*')
            ->selectRaw('COALESCE(saldo_producto.cantidad_fisica, 0) AS existencias_alcance')
            ->selectRaw('COALESCE(saldo_producto.cantidad_reservada, 0) AS existencias_reservadas')
            ->where('productos.empresa_id', $alcance['empresa_id']);

        if (! $incluirInactivos) {
            $consulta->where('productos.activo', true);
        }

        if (! $incluirSinExistencias) {
            $consulta->whereNotNull('saldo_producto.producto_id');
        }

        $buscar = $filtros['buscar'] ?? null;
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
        if (($filtros['categoria'] ?? null) !== null) {
            $consulta->where('productos.categoria_id', $filtros['categoria']);
        }
        if (($filtros['proveedor'] ?? null) !== null) {
            $consulta->whereHas('suppliers', fn (Builder $proveedores): Builder => $proveedores->whereKey($filtros['proveedor']));
        }

        $estado = $filtros['estado'] ?? null;
        if ($estado === 'activo') {
            $consulta->where('productos.activo', true);
        } elseif ($estado === 'inactivo') {
            $consulta->where('productos.activo', false);
        } elseif ($estado === 'con_existencias') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) > ?', [0]);
        } elseif ($estado === 'sin_existencias') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) <= ?', [0]);
        }

        $stock = $filtros['stock'] ?? null;
        if ($stock === 'sin_stock') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) <= ?', [0]);
        } elseif ($stock === 'con_stock') {
            $consulta->whereRaw('COALESCE(saldo_producto.cantidad_fisica, 0) > ?', [0]);
        }

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
        $saldos = DB::table('existencias_almacen')->select('producto_id')
            ->selectRaw('SUM(cantidad_fisica) AS unidades')
            ->where('empresa_id', $alcance['empresa_id'])
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('sede_id', $alcance['sede_id']))
            ->groupBy('producto_id');

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
        if ($productos === []) {
            return collect();
        }

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
        $saldos = DB::table('existencias_almacen')->select('producto_id')
            ->selectRaw('SUM(cantidad_fisica) AS unidades')
            ->where('empresa_id', $alcance['empresa_id'])
            ->when($alcance['sede_id'] !== null, fn ($consulta) => $consulta->where('sede_id', $alcance['sede_id']))
            ->groupBy('producto_id');
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
     * @param  array<int, int|string>  $identificadores
     * @return array<int, int>
     */
    public function disponibilidadParaOperacion(User $usuario, array $identificadores): array
    {
        $this->authorization->assertAllowed($usuario, 'gestion.pedidos.crear');
        $ubicacion = $this->authorization->operationLocation($usuario);
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
        $ids = collect($identificadores)->map(fn (int|string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)->unique()->values()->all();
        if ($ids === []) {
            return [];
        }
        if (! DB::table('almacenes')->where('id', $ubicacion['almacen_id'])
            ->where('activo', true)->where('permite_venta', true)->exists()) {
            return array_fill_keys($ids, 0);
        }

        $productos = Producto::query()->whereIn('id', $ids)->where('empresa_id', $ubicacion['empresa_id'])
            ->where('activo', true)->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        $saldos = DB::table('existencias_almacen')->where('empresa_id', $ubicacion['empresa_id'])
            ->where('sede_id', $ubicacion['sede_id'])->where('almacen_id', $ubicacion['almacen_id'])
            ->whereIn('producto_id', $productos)->get(['producto_id', 'variante_id', 'cantidad_fisica', 'cantidad_reservada'])
            ->groupBy('producto_id');
        $variantes = $saldos->flatten(1)->pluck('variante_id')->filter()->unique()->values()->all();
        $variantesSerializadas = $variantes !== [] && Schema::hasTable('variantes_productos') && Schema::hasColumn('variantes_productos', 'controla_serie')
            ? DB::table('variantes_productos')->whereIn('id', $variantes)->where('controla_serie', true)->pluck('id')->map(fn (mixed $id): int => (int) $id)->all()
            : $variantes;
        $productosSerializados = Schema::hasTable('dispositivos')
            ? DB::table('dispositivos')->whereIn('producto_id', $productos)->distinct()->pluck('producto_id')->map(fn (mixed $id): int => (int) $id)->all()
            : [];

        $disponibilidad = [];
        foreach ($productos as $productoId) {
            $registros = $saldos->get($productoId, collect());
            $saldo = $registros->count() === 1 ? $registros->first() : null;
            if ($saldo === null || in_array($productoId, $productosSerializados, true)
                || ($saldo->variante_id !== null && in_array((int) $saldo->variante_id, $variantesSerializadas, true))) {
                $disponibilidad[$productoId] = 0;

                continue;
            }
            $fisica = (float) $saldo->cantidad_fisica;
            $reservada = (float) $saldo->cantidad_reservada;
            $disponibilidad[$productoId] = $fisica >= 0 && $fisica === floor($fisica) && $reservada >= 0
                ? max(0, (int) floor($fisica - $reservada)) : 0;
        }

        return $disponibilidad;
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function registerNewProduct(Producto $product, User $user): void
    {
        abort_unless(DB::transactionLevel() > 0 && Schema::hasTable('existencias_almacen'), 503, 'El inventario requiere una transacción y un almacén configurado.');
        $location = $this->authorization->operationLocation($user);
        abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null && $location['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
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
     * Ejecuta la operación adjust definida por la clase ServicioInventario.
     *
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @param  mixed  $movementType  Parámetro recibido por la operación.
     * @param  mixed  $quantity  Parámetro recibido por la operación.
     * @param  mixed  $notes  Parámetro recibido por la operación.
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function adjust(Producto $product, string $movementType, int $quantity, string $notes, User $user): ?MovimientoInventario
    {
        $this->authorization->assertAllowed($user, 'gestion.inventario.ajustar');

        return DB::transaction(function () use ($product, $movementType, $quantity, $notes, $user): ?MovimientoInventario {
            $lockedProduct = Producto::whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            $movement = $this->changeLockedProduct($lockedProduct, $movementType, $quantity, $notes, $user);
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
     * Registra la información recibida y coordina su persistencia.
     *
     * @return mixed Resultado de la operación.
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

        return DB::transaction(function () use ($producto, $varianteId, $almacenId, $tipoMovimiento, $cantidad, $notas, $usuario): ?MovimientoInventario {
            $ubicacion = DB::table('almacenes')
                ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
                ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
                ->where('almacenes.id', $almacenId)
                ->where('almacenes.activo', true)
                ->where('sedes.activa', true)
                ->where('empresas.activo', true)
                ->first(['empresas.id AS empresa_id', 'sedes.id AS sede_id', 'almacenes.id AS almacen_id']);

            abort_unless($ubicacion !== null && (int) $producto->empresa_id === (int) $ubicacion->empresa_id, 403);
            abort_unless($this->authorization->allowsRecord(
                $usuario,
                'gestion.inventario.ajustar',
                (int) $ubicacion->empresa_id,
                (int) $ubicacion->sede_id,
            ), 403);

            $productoBloqueado = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            $tieneVariantes = DB::table('variantes_productos')->where('producto_id', $productoBloqueado->id)->exists();
            if ($varianteId === null) {
                abort_if($tieneVariantes, 422, 'Selecciona la variante del producto antes de registrar el movimiento.');
            } else {
                $variante = DB::table('variantes_productos')
                    ->where('id', $varianteId)
                    ->where('producto_id', $productoBloqueado->id)
                    ->first(['estado']);

                abort_unless($variante !== null, 422, 'La variante no pertenece al producto seleccionado.');
                abort_if($tipoMovimiento === 'entrada' && $variante->estado !== 'activo', 422, 'No se pueden ingresar existencias a una variante inactiva.');
            }

            $movimiento = $this->changeLockedProductInWarehouse(
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
     * Ejecuta la operación changeLockedProduct definida por la clase ServicioInventario.
     *
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @param  mixed  $movementType  Parámetro recibido por la operación.
     * @param  mixed  $quantity  Parámetro recibido por la operación.
     * @param  mixed  $notes  Parámetro recibido por la operación.
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function changeLockedProduct(Producto $product, string $movementType, int $quantity, string $notes, User $user): ?MovimientoInventario
    {
        abort_unless(DB::transactionLevel() > 0, 503, 'El movimiento de inventario requiere una transacción.');
        abort_if($quantity < 0 || ($movementType !== 'ajuste' && $quantity === 0), 422, 'La cantidad del movimiento no es válida.');
        $location = $this->authorization->operationLocation($user);
        abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null && $location['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
        abort_unless((int) $product->empresa_id === $location['empresa_id'], 403);

        $balances = DB::table('existencias_almacen')
            ->where('empresa_id', $location['empresa_id'])
            ->where('sede_id', $location['sede_id'])
            ->where('almacen_id', $location['almacen_id'])
            ->where('producto_id', $product->id)
            ->lockForUpdate()
            ->get(['id', 'variante_id', 'cantidad_fisica', 'cantidad_reservada']);
        abort_unless($balances->count() === 1, 409, 'El producto requiere conciliar o seleccionar su existencia por variante.');
        $balance = $balances->first();
        $productBalances = DB::table('existencias_almacen')->where('producto_id', $product->id)->where('empresa_id', $location['empresa_id']);
        abort_if((clone $productBalances)->whereNull('variante_id')->exists() && (clone $productBalances)->whereNotNull('variante_id')->exists(), 409, 'Las existencias por producto y variante requieren conciliación.');
        abort_if((float) $balance->cantidad_fisica !== floor((float) $balance->cantidad_fisica), 422, 'El formulario no admite existencias fraccionarias.');
        $previousStock = (int) $balance->cantidad_fisica;
        $nextStock = match ($movementType) {
            'entrada' => $previousStock + $quantity,
            'salida' => $previousStock - $quantity,
            'ajuste' => $quantity,
            default => throw new \InvalidArgumentException('Tipo de movimiento inválido.'),
        };

        abort_if($nextStock < 0, 422, 'La salida supera el stock disponible.');

        abort_if((float) $balance->cantidad_reservada > $nextStock, 422, 'El movimiento dejaría reservas sin respaldo.');
        $variantId = $balance->variante_id === null ? null : (int) $balance->variante_id;
        if ($movementType === 'ajuste' && $nextStock === $previousStock) {
            return null;
        }

        DB::table('existencias_almacen')->where('id', $balance->id)->update(['cantidad_fisica' => $nextStock]);

        $total = (float) DB::table('existencias_almacen')->where('producto_id', $product->id)->where('empresa_id', $location['empresa_id'])->sum('cantidad_fisica');
        abort_if($total !== floor($total) || $total > 4294967295, 422, 'No es posible actualizar la proyección heredada de existencias.');
        $product->update(['existencias' => (int) $total]);
        if ($variantId !== null) {
            $variantTotal = (float) DB::table('existencias_almacen')->where('variante_id', $variantId)->sum('cantidad_fisica');
            abort_if($variantTotal !== floor($variantTotal) || $variantTotal > 4294967295, 422, 'No es posible actualizar la proyección heredada de la variante.');
            DB::table('variantes_productos')->where('id', $variantId)->update(['existencias' => (int) $variantTotal]);
        }

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
     * Ejecuta la operación changeLockedProductInWarehouse definida por la clase ServicioInventario.
     *
     * @return mixed Resultado de la operación.
     */
    public function changeLockedProductInWarehouse(
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
        abort_unless(DB::transactionLevel() > 0, 503, 'El movimiento de inventario requiere una transaccion.');
        abort_if($quantity < 0 || ($movementType !== 'ajuste' && $quantity === 0), 422, 'La cantidad del movimiento no es valida.');
        abort_unless((int) $product->empresa_id === $companyId, 403);
        $warehouseIsValid = DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('almacenes.id', $warehouseId)
            ->where('almacenes.sede_id', $siteId)
            ->where('sedes.empresa_id', $companyId)
            ->where('almacenes.activo', true)
            ->where('sedes.activa', true)
            ->exists();
        abort_unless($warehouseIsValid, 422, 'El almacen no pertenece a una sede activa de la empresa.');

        $balanceQuery = DB::table('existencias_almacen')
            ->where('empresa_id', $companyId)
            ->where('sede_id', $siteId)
            ->where('almacen_id', $warehouseId)
            ->where('producto_id', $product->id);
        if ($variantId === null) {
            $balanceQuery->whereNull('variante_id');
        } else {
            $balanceQuery->where('variante_id', $variantId);
        }
        $balance = $balanceQuery->lockForUpdate()->first(['id', 'cantidad_fisica', 'cantidad_reservada']);
        if ($balance === null && $createMissingBalance && $movementType === 'ajuste' && $quantity === 0) {
            return null;
        }
        if ($balance === null && $createMissingBalance) {
            DB::table('existencias_almacen')->insertOrIgnore([
                'almacen_id' => $warehouseId, 'variante_id' => $variantId, 'producto_id' => $product->id,
                'empresa_id' => $companyId, 'sede_id' => $siteId, 'cantidad_fisica' => 0, 'cantidad_reservada' => 0,
            ]);
            $balanceQuery = DB::table('existencias_almacen')->where('empresa_id', $companyId)
                ->where('sede_id', $siteId)->where('almacen_id', $warehouseId)->where('producto_id', $product->id);
            if ($variantId === null) {
                $balanceQuery->whereNull('variante_id');
            } else {
                $balanceQuery->where('variante_id', $variantId);
            }
            $balance = $balanceQuery->lockForUpdate()->first(['id', 'cantidad_fisica', 'cantidad_reservada']);
        }
        abort_unless($balance !== null, 409, 'No existe saldo para el producto o variante en el almacen.');
        abort_if((float) $balance->cantidad_fisica !== floor((float) $balance->cantidad_fisica), 422, 'El formulario no admite existencias fraccionarias.');

        $previousStock = (int) $balance->cantidad_fisica;
        $nextStock = match ($movementType) {
            'entrada' => $previousStock + $quantity,
            'salida' => $previousStock - $quantity,
            'ajuste' => $quantity,
            default => throw new \InvalidArgumentException('Tipo de movimiento invalido.'),
        };
        abort_if($nextStock < 0, 422, 'La salida supera el stock disponible.');
        abort_if((float) $balance->cantidad_reservada > $nextStock, 422, 'El movimiento dejaria reservas sin respaldo.');
        if ($movementType === 'ajuste' && $nextStock === $previousStock) {
            return null;
        }

        DB::table('existencias_almacen')->where('id', $balance->id)->update(['cantidad_fisica' => $nextStock]);
        $total = (float) DB::table('existencias_almacen')->where('producto_id', $product->id)->where('empresa_id', $companyId)->sum('cantidad_fisica');
        abort_if($total !== floor($total) || $total > 4294967295, 422, 'No es posible actualizar la proyeccion heredada de existencias.');
        $product->update(['existencias' => (int) $total]);
        if ($variantId !== null) {
            $variantTotal = (float) DB::table('existencias_almacen')->where('variante_id', $variantId)->sum('cantidad_fisica');
            abort_if($variantTotal !== floor($variantTotal) || $variantTotal > 4294967295, 422, 'No es posible actualizar la proyeccion heredada de la variante.');
            DB::table('variantes_productos')->where('id', $variantId)->update(['existencias' => (int) $variantTotal]);
        }

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
