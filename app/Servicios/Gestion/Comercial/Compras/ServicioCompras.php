<?php

namespace App\Servicios\Gestion\Comercial\Compras;

use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioCompras dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioCompras
{
    private const MAX_CENTIMOS = 999999999999;

    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly RegistradorAuditoria $auditoria,
        private readonly ServicioAutorizacion $autorizacion,
    ) {}

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $datos  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $token  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Compra Resultado de la operación.
     */
    public function registrar(array $datos, User $actor, string $token, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.crear');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasColumn('registros_auditoria', 'identificador_universal_evento'), 503, 'No está disponible la protección contra compras duplicadas.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 422, 'La solicitud de compra no es válida.');
        // Guarda en $ubicacion el resultado necesario para continuar la operacion.
        $ubicacion = $this->autorizacion->operationLocation($actor);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']), 403);
        // Guarda en $hash el resultado necesario para continuar la operacion.
        $hash = hash('sha256', 'compra:'.$actor->getKey().':'.$token);
        // Guarda en $evento el resultado necesario para continuar la operacion.
        $evento = substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-4'.substr($hash, 13, 3).'-a'.substr($hash, 17, 3).'-'.substr($hash, 20, 12);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        return DB::transaction(function () use ($datos, $actor, $ip, $ubicacion, $evento): Compra {
            DB::table('empresas')->where('id', $ubicacion['empresa_id'])->lockForUpdate()->firstOrFail();
            // Prepara en $previo la consulta que obtiene o modifica los datos necesarios.
            $previo = DB::table('registros_auditoria')->where('identificador_universal_evento', $evento)->lockForUpdate()->first();
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($previo !== null) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($previo->accion === 'compra.registrada' && $previo->entidad === 'compras' && (int) $previo->usuario_id === (int) $actor->id, 409, 'La solicitud ya fue utilizada.');
                // Prepara en $compra la consulta que obtiene o modifica los datos necesarios.
                $compra = Compra::query()->whereKey($previo->entidad_id)->lockForUpdate()->firstOrFail();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless((int) $compra->empresa_id === $ubicacion['empresa_id'] && (int) $compra->sede_id === $ubicacion['sede_id']
                    && (int) $compra->almacen_id === $ubicacion['almacen_id'] && (int) $compra->proveedor_id === (int) $datos['proveedor_id']
                    && $compra->fecha_compra->toDateString() === $datos['fecha_compra'], 409, 'La solicitud ya corresponde a otra compra.');
                $anteriores = $compra->details()->orderBy('producto_id')->get()->map(fn ($linea): array => [(int) $linea->producto_id, $linea->variante_id === null ? null : (int) $linea->variante_id, (int) $linea->cantidad, $this->centimos((string) $linea->costo_unitario)])->all();
                $solicitados = collect($datos['items'])->sortBy('producto_id')->map(fn (array $linea): array => [(int) $linea['producto_id'], isset($linea['variante_id']) ? (int) $linea['variante_id'] : null, (int) $linea['cantidad'], $this->centimos((string) $linea['costo_unitario'])])->values()->all();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($anteriores === $solicitados, 409, 'La solicitud ya corresponde a otros productos o costos.');

                return $compra;
            }

            // Prepara en $proveedor la consulta que obtiene o modifica los datos necesarios.
            $proveedor = Proveedor::query()->whereKey($datos['proveedor_id'])->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->first();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($proveedor !== null, 422, 'El proveedor no está activo en la empresa autorizada.');
            // Guarda en $items el resultado necesario para continuar la operacion.
            $items = collect($datos['items'])->sortBy('producto_id')->values();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($items->isEmpty() || $items->count() > 100 || $items->pluck('producto_id')->unique()->count() !== $items->count(), 422, 'Los productos de la compra no son válidos.');
            // Prepara en $productos la consulta que obtiene o modifica los datos necesarios.
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            // Agrupa en $lineas los datos que se utilizaran en el siguiente paso.
            $lineas = [];
            // Guarda en $total el resultado necesario para continuar la operacion.
            $total = 0;
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($items as $item) {
                // Guarda en $producto el resultado de la consulta ejecutada.
                $producto = $productos->get((int) $item['producto_id']);
                // Guarda en $cantidad el resultado necesario para continuar la operacion.
                $cantidad = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === $ubicacion['empresa_id'], 422, 'Un producto no está activo en la empresa autorizada.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($cantidad === false || $cantidad < 1 || $cantidad > 1000, 422, 'La cantidad no es válida.');
                // Prepara en $variantesProducto la consulta que obtiene o modifica los datos necesarios.
                $variantesProducto = DB::table('variantes_productos')->where('producto_id', $producto->id);
                $varianteId = isset($item['variante_id']) && $item['variante_id'] !== '' ? (int) $item['variante_id'] : null;
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ((clone $variantesProducto)->exists()) {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($varianteId !== null, 422, 'Selecciona la variante que se comprará.');
                    // Guarda en $varianteValida el resultado necesario para continuar la operacion.
                    $varianteValida = (clone $variantesProducto)->where('id', $varianteId)->where('estado', 'activo')->exists();
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($varianteValida, 422, 'La variante seleccionada no está activa o no pertenece al producto.');
                } else {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($varianteId === null, 422, 'El producto seleccionado no tiene variantes activas.');
                }
                // Guarda en $costo el resultado necesario para continuar la operacion.
                $costo = $this->centimos((string) $item['costo_unitario']);
                // Guarda en $descuento el resultado necesario para continuar la operacion.
                $descuento = $this->centimos(number_format((float) ($item['descuento'] ?? 0), 2, '.', ''));
                // Guarda en $impuesto el resultado necesario para continuar la operacion.
                $impuesto = $this->centimos(number_format((float) ($item['impuesto'] ?? 0), 2, '.', ''));
                // Guarda en $importe el resultado necesario para continuar la operacion.
                $importe = ($costo * $cantidad) - $descuento + $impuesto;
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($importe < 0, 422, 'El descuento no puede superar el importe de la línea.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($importe > self::MAX_CENTIMOS - $total, 422, 'La compra supera el importe permitido.');
                $total += $importe;
                $linea = ['producto_id' => $producto->id, 'variante_id' => $varianteId, 'cantidad' => $cantidad, 'costo_unitario' => $this->importe($costo)];
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if (Schema::hasColumn('detalles_compras', 'descuento')) {
                    $linea['descuento'] = $this->importe($descuento);
                    $linea['impuesto'] = $this->importe($impuesto);
                    $linea['total_linea'] = $this->importe($importe);
                }
                $lineas[] = $linea;
            }
            // Agrupa en $compraDatos los datos que se utilizaran en el siguiente paso.
            $compraDatos = [
                ...$ubicacion,
                'proveedor_id' => $proveedor->id,
                'creado_por_usuario_id' => $actor->id,
                'total' => $this->importe($total),
                'estado' => 'Pendiente',
                'fecha_compra' => $datos['fecha_compra'],
                'activo' => true,
                'creado_en' => now(),
            ];
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (Schema::hasColumn('compras', 'numero_referencia')) {
                $compraDatos['numero_referencia'] = $datos['numero_referencia'] ?? null;
                $compraDatos['moneda'] = $datos['moneda'] ?? 'PEN';
            }
            // Crea y guarda en $compra el registro gestionado por este servicio.
            $compra = Compra::create($compraDatos);
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($lineas as $linea) {
                $compra->details()->create($linea);
            }
            $this->auditoria->record(
                $actor->id,
                'compra.registrada',
                'compras',
                $compra->id,
                null,
                ['proveedor_id' => $proveedor->id, 'total' => $compra->total, 'items' => count($lineas)],
                $ip,
                scope: ['empresa_id' => $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id']],
                eventId: $evento
            );

            return $compra;
        }, 3);
    }

    /**
     * Ejecuta la operación aprobar definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Compra Resultado de la operación.
     */
    /**
     * Actualiza una compra pendiente sin crear un nuevo encabezado ni afectar inventario.
     *
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Compra $compra, array $datos, User $actor, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.actualizar');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($compra, $datos, $actor, $ip): Compra {
            // Prepara en $bloqueada la consulta que obtiene o modifica los datos necesarios.
            $bloqueada = Compra::query()->whereKey($compra->getKey())->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.actualizar', (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($bloqueada->activo && $bloqueada->estado === 'Pendiente', 409, 'Solo se pueden editar compras pendientes.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $bloqueada->id)->exists(), 409, 'La compra ya afect— inventario y requiere un procedimiento de ajuste.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (Schema::hasTable('pagos')) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(DB::table('pagos')->where('compra_id', $bloqueada->id)->exists(), 409, 'La compra tiene pagos asociados y no puede editarse directamente.');
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (Schema::hasTable('cuentas_pagar')) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(DB::table('cuentas_pagar')->where('compra_id', $bloqueada->id)->exists(), 409, 'La compra tiene una cuenta por pagar asociada y requiere conciliación.');
            }

            // Prepara en $proveedor la consulta que obtiene o modifica los datos necesarios.
            $proveedor = Proveedor::query()->whereKey($datos['proveedor_id'])->where('empresa_id', $bloqueada->empresa_id)->where('activo', true)->first();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($proveedor !== null, 422, 'El proveedor no está activo en la empresa de la compra.');
            // Guarda en $items el resultado necesario para continuar la operacion.
            $items = collect($datos['items'])->sortBy('producto_id')->values();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($items->isEmpty() || $items->count() > 100 || $items->pluck('producto_id')->unique()->count() !== $items->count(), 422, 'Los productos de la compra no son válidos.');
            // Prepara en $productos la consulta que obtiene o modifica los datos necesarios.
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            // Agrupa en $lineas los datos que se utilizaran en el siguiente paso.
            $lineas = [];
            // Guarda en $total el resultado necesario para continuar la operacion.
            $total = 0;
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($items as $item) {
                // Guarda en $producto el resultado de la consulta ejecutada.
                $producto = $productos->get((int) $item['producto_id']);
                // Guarda en $cantidad el resultado necesario para continuar la operacion.
                $cantidad = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === (int) $bloqueada->empresa_id, 422, 'Un producto no está activo en la empresa de la compra.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($cantidad === false || $cantidad < 1 || $cantidad > 1000, 422, 'La cantidad no es válida.');
                // Prepara en $variantesProducto la consulta que obtiene o modifica los datos necesarios.
                $variantesProducto = DB::table('variantes_productos')->where('producto_id', $producto->id);
                $varianteId = isset($item['variante_id']) && $item['variante_id'] !== '' ? (int) $item['variante_id'] : null;
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ((clone $variantesProducto)->exists()) {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($varianteId !== null && (clone $variantesProducto)->where('id', $varianteId)->where('estado', 'activo')->exists(), 422, 'La variante seleccionada no es válida.');
                } else {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($varianteId === null, 422, 'El producto seleccionado no tiene variantes activas.');
                }
                // Guarda en $costo el resultado necesario para continuar la operacion.
                $costo = $this->centimos((string) $item['costo_unitario']);
                // Guarda en $descuento el resultado necesario para continuar la operacion.
                $descuento = $this->centimos(number_format((float) ($item['descuento'] ?? 0), 2, '.', ''));
                // Guarda en $impuesto el resultado necesario para continuar la operacion.
                $impuesto = $this->centimos(number_format((float) ($item['impuesto'] ?? 0), 2, '.', ''));
                // Guarda en $importe el resultado necesario para continuar la operacion.
                $importe = ($costo * $cantidad) - $descuento + $impuesto;
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($importe < 0, 422, 'El descuento no puede superar el importe de la línea.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($importe > self::MAX_CENTIMOS - $total, 422, 'La compra supera el importe permitido.');
                $total += $importe;
                $linea = ['producto_id' => $producto->id, 'variante_id' => $varianteId, 'cantidad' => $cantidad, 'costo_unitario' => $this->importe($costo)];
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if (Schema::hasColumn('detalles_compras', 'descuento')) {
                    $linea['descuento'] = $this->importe($descuento);
                    $linea['impuesto'] = $this->importe($impuesto);
                    $linea['total_linea'] = $this->importe($importe);
                }
                $lineas[] = $linea;
            }

            $anteriores = ['proveedor_id' => $bloqueada->proveedor_id, 'fecha_compra' => $bloqueada->fecha_compra?->toDateString(), 'total' => $bloqueada->total, 'items' => $bloqueada->details()->orderBy('id')->get()->map(fn ($item): array => $item->only(['producto_id', 'variante_id', 'cantidad', 'costo_unitario', 'descuento', 'impuesto', 'total_linea']))->all()];
            $actualizacion = ['proveedor_id' => $proveedor->id, 'fecha_compra' => $datos['fecha_compra'], 'total' => $this->importe($total)];
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (Schema::hasColumn('compras', 'numero_referencia')) {
                $actualizacion['numero_referencia'] = $datos['numero_referencia'] ?? null;
                $actualizacion['moneda'] = $datos['moneda'] ?? 'PEN';
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (Schema::hasColumn('compras', 'actualizado_en')) {
                $actualizacion['actualizado_en'] = now();
            }
            $bloqueada->update($actualizacion);
            $bloqueada->details()->delete();
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($lineas as $linea) {
                $bloqueada->details()->create($linea);
            }
            $nuevos = ['proveedor_id' => $proveedor->id, 'fecha_compra' => $datos['fecha_compra'], 'total' => $bloqueada->total, 'items' => $lineas];
            $this->auditoria->record(
                $actor->id,
                'compra.actualizada',
                'compras',
                $bloqueada->id,
                $anteriores,
                $nuevos,
                $ip,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id]
            );

            return $bloqueada->fresh(['supplier', 'details.product', 'details.variante']);
        }, 3);
    }

    public function aprobar(Compra $compra, User $actor, ?string $ip): Compra
    {
        return $this->cambiarEstado($compra, $actor, $ip, false);
    }

    /**
     * Ejecuta la operación recibir definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Compra Resultado de la operación.
     */
    public function recibir(Compra $compra, User $actor, ?string $ip): Compra
    {
        return $this->cambiarEstado($compra, $actor, $ip, true);
    }

    /**
     * Ejecuta la operación anular definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Compra Resultado de la operación.
     */
    public function anular(Compra $compra, User $actor, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.anular');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($compra, $actor, $ip): Compra {
            // Prepara en $bloqueada la consulta que obtiene o modifica los datos necesarios.
            $bloqueada = Compra::query()->whereKey($compra->getKey())->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.anular', (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($bloqueada->activo && $bloqueada->estado === 'Pendiente', 409, 'Solo se pueden anular compras pendientes que aún no hayan sido aprobadas o recibidas.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $bloqueada->id)->exists(), 409, 'La compra ya tiene movimientos de inventario y requiere conciliación.');
            $bloqueada->update(['activo' => false, 'estado' => 'Cancelada']);
            $this->auditoria->record(
                $actor->id,
                'compra.anulada',
                'compras',
                $bloqueada->id,
                ['activo' => true, 'estado' => 'Pendiente'],
                ['activo' => false, 'estado' => 'Cancelada'],
                $ip,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id]
            );

            return $bloqueada;
        }, 3);
    }

    /**
     * @param  array<int, array{detalle_compra_id:int|string,cantidad:int|string,identificadores?:string|null}>  $items
     */
    public function registrarRecepcionParcial(Compra $compra, array $items, ?string $observaciones, string $token, User $actor, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.recibir');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasColumn('registros_auditoria', 'identificador_universal_evento'), 503, 'No esta disponible la proteccion contra recepciones duplicadas.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 422, 'La solicitud de recepcion no es valida.');

        // Guarda en $ubicacion el resultado necesario para continuar la operacion.
        $ubicacion = $this->autorizacion->operationLocation($actor);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almacen autorizado disponible.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.recibir', $ubicacion['empresa_id'], $ubicacion['sede_id']), 403);

        // Guarda en $hash el resultado necesario para continuar la operacion.
        $hash = hash('sha256', 'recepcion-compra:'.$actor->getKey().':'.$compra->getKey().':'.$token);
        // Guarda en $evento el resultado necesario para continuar la operacion.
        $evento = substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-4'.substr($hash, 13, 3).'-a'.substr($hash, 17, 3).'-'.substr($hash, 20, 12);
        // Guarda en $itemsNormalizados el resultado necesario para continuar la operacion.
        $itemsNormalizados = collect($items)
            ->map(function (array $item): array {
                // Guarda en $cantidad el resultado necesario para continuar la operacion.
                $cantidad = (int) $item['cantidad'];
                // Guarda en $identificadores el resultado necesario para continuar la operacion.
                $identificadores = $this->normalizarIdentificadores((string) ($item['identificadores'] ?? ''));
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($cantidad === 0 && $identificadores !== [], 422, 'No registres identificadores cuando la cantidad recibida sea cero.');

                return [
                    'detalle_compra_id' => (int) $item['detalle_compra_id'],
                    'cantidad' => $cantidad,
                    'identificadores' => $identificadores,
                ];
            })
            ->filter(fn (array $item): bool => $item['cantidad'] > 0)
            ->sortBy('detalle_compra_id')
            ->values()
            ->all();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($itemsNormalizados === [], 422, 'Indica al menos una cantidad recibida mayor que cero.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if(collect($itemsNormalizados)->pluck('detalle_compra_id')->unique()->count() !== count($itemsNormalizados), 422, 'Un detalle fue enviado mas de una vez.');
        $itemsParaAuditoria = array_map(static fn (array $item): array => [
            'detalle_compra_id' => $item['detalle_compra_id'],
            'cantidad' => $item['cantidad'],
            'identificadores_cantidad' => count($item['identificadores']),
            'identificadores_hash' => hash('sha256', json_encode($item['identificadores'], JSON_THROW_ON_ERROR)),
        ], $itemsNormalizados);

        try {
            // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
            return DB::transaction(function () use ($compra, $itemsNormalizados, $itemsParaAuditoria, $observaciones, $actor, $ip, $ubicacion, $evento): Compra {
                // Prepara en $bloqueada la consulta que obtiene o modifica los datos necesarios.
                $bloqueada = Compra::query()->whereKey($compra->getKey())->lockForUpdate()->firstOrFail();
                // Prepara en $previo la consulta que obtiene o modifica los datos necesarios.
                $previo = DB::table('registros_auditoria')->where('identificador_universal_evento', $evento)->lockForUpdate()->first();

                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($previo !== null) {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($previo->accion === 'compra.recepcion_parcial' && $previo->entidad === 'compras'
                        && (int) $previo->usuario_id === (int) $actor->id && (int) $previo->entidad_id === (int) $bloqueada->id, 409, 'La solicitud de recepcion ya fue utilizada.');
                    // Guarda en $datosPrevios el resultado necesario para continuar la operacion.
                    $datosPrevios = json_decode((string) $previo->valores_nuevos, true);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless(is_array($datosPrevios) && ($datosPrevios['items'] ?? null) === $itemsParaAuditoria, 409, 'La solicitud ya corresponde a otras cantidades o identificadores.');

                    return $bloqueada;
                }

                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless((int) $bloqueada->empresa_id === $ubicacion['empresa_id'] && (int) $bloqueada->sede_id === $ubicacion['sede_id']
                    && (int) $bloqueada->almacen_id === $ubicacion['almacen_id'], 409, 'La recepcion requiere el almacen original de la compra.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.recibir', (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($bloqueada->activo && $bloqueada->estado === 'Aprobada', 409, 'La compra debe estar aprobada y pendiente de recepcion.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless(DB::table('registros_auditoria')->where('accion', 'compra.registrada')->where('entidad', 'compras')->where('entidad_id', $bloqueada->id)->exists(), 409, 'La compra requiere conciliacion antes de continuar.');

                // Guarda en $lineas el resultado de la consulta ejecutada.
                $lineas = $bloqueada->details()->orderBy('id')->lockForUpdate()->get();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($lineas->isEmpty() || $lineas->pluck('producto_id')->unique()->count() !== $lineas->count(), 409, 'Los detalles de compra requieren conciliacion.');
                // Guarda en $lineasPorId el resultado necesario para continuar la operacion.
                $lineasPorId = $lineas->keyBy('id');
                // Guarda en $recibidasAntes el resultado necesario para continuar la operacion.
                $recibidasAntes = $this->cantidadesRecibidasPorDetalle($bloqueada, $lineas);
                // Recorre los elementos recibidos para validar o procesar cada registro.
                foreach ($lineas as $linea) {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_if((int) ($recibidasAntes[$linea->id] ?? 0) > (int) $linea->cantidad, 409, 'El inventario excede la cantidad comprada.');
                }
                // Prepara en $productos la consulta que obtiene o modifica los datos necesarios.
                $productos = Producto::query()->whereIn('id', $lineas->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                // Recorre los elementos recibidos para validar o procesar cada registro.
                foreach ($itemsNormalizados as $item) {
                    // Guarda en $linea el resultado de la consulta ejecutada.
                    $linea = $lineasPorId->get($item['detalle_compra_id']);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($linea !== null, 422, 'Un detalle no pertenece a esta compra.');
                    // Guarda en $cantidadPendiente el resultado necesario para continuar la operacion.
                    $cantidadPendiente = (int) $linea->cantidad - (int) ($recibidasAntes[$linea->id] ?? 0);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_if($item['cantidad'] > $cantidadPendiente, 422, 'La cantidad recibida supera lo pendiente para un producto.');
                }

                // Recorre los elementos recibidos para validar o procesar cada registro.
                foreach ($itemsNormalizados as $item) {
                    // Guarda en $linea el resultado de la consulta ejecutada.
                    $linea = $lineasPorId->get($item['detalle_compra_id']);
                    // Guarda en $producto el resultado de la consulta ejecutada.
                    $producto = $productos->get((int) $linea->producto_id);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($producto instanceof Producto && (int) $producto->empresa_id === (int) $bloqueada->empresa_id, 409, 'Un producto de la compra requiere conciliacion.');

                    // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                    $this->registrarEntradaDeCompra($bloqueada, $linea, $producto, $item['cantidad'], $observaciones, $actor, $item['identificadores']);
                }

                // Guarda en $recibidasDespues el resultado necesario para continuar la operacion.
                $recibidasDespues = $this->cantidadesRecibidasPorDetalle($bloqueada, $lineas);
                $completa = $lineas->every(fn ($linea): bool => (int) ($recibidasDespues[$linea->id] ?? 0) === (int) $linea->cantidad);
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if (! $completa) {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless(is_string($observaciones) && trim($observaciones) !== '', 422, 'Registra la discrepancia o el motivo de la recepcion parcial.');
                }

                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($completa) {
                    $bloqueada->update(['estado' => 'Recibida']);
                }

                // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                $this->comprobarRecepcion($bloqueada, $completa);
                $this->auditoria->record(
                    $actor->id,
                    'compra.recepcion_parcial',
                    'compras',
                    $bloqueada->id,
                    ['estado' => 'Aprobada', 'cantidades_recibidas' => $recibidasAntes],
                    ['estado' => $bloqueada->estado, 'items' => $itemsParaAuditoria, 'cantidades_recibidas' => $recibidasDespues],
                    $ip,
                    observations: $observaciones,
                    scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id],
                    eventId: $evento,
                );

                return $bloqueada;
            }, 3);
        } catch (UniqueConstraintViolationException $excepcion) {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort(409, 'Uno de los IMEI o números de serie ya está registrado. No se guard— la recepción.');
        }
    }

    /**
     * Ejecuta la operación cambiarEstado definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @param  mixed  $recibir  Parámetro recibido por la operación.
     * @return Compra Resultado de la operación.
     */
    private function cambiarEstado(Compra $compra, User $actor, ?string $ip, bool $recibir): Compra
    {
        // Guarda en $permiso el resultado necesario para continuar la operacion.
        $permiso = $recibir ? 'gestion.compras.recibir' : 'gestion.compras.aprobar';
        $this->autorizacion->assertAllowed($actor, $permiso);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($compra, $actor, $ip, $recibir, $permiso): Compra {
            // Prepara en $bloqueada la consulta que obtiene o modifica los datos necesarios.
            $bloqueada = Compra::query()->whereKey($compra->id)->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($this->autorizacion->allowsRecord($actor, $permiso, (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            // Guarda en $ubicacion el resultado necesario para continuar la operacion.
            $ubicacion = $this->autorizacion->operationLocation($actor);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless((int) $bloqueada->empresa_id === $ubicacion['empresa_id'] && (int) $bloqueada->sede_id === $ubicacion['sede_id']
                && (int) $bloqueada->almacen_id === $ubicacion['almacen_id'], 409, 'La operación requiere el almacén original de la compra.');
            // Guarda en $destino el resultado necesario para continuar la operacion.
            $destino = $recibir ? 'Recibida' : 'Aprobada';
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($bloqueada->estado === $destino) {
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($recibir) {
                    // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                    $this->comprobarRecepcion($bloqueada, true);
                }

                return $bloqueada;
            }
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($bloqueada->activo && $bloqueada->estado === ($recibir ? 'Aprobada' : 'Pendiente'), 409, 'El estado de la compra no permite esta operación.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless(DB::table('registros_auditoria')->where('accion', 'compra.registrada')->where('entidad', 'compras')->where('entidad_id', $bloqueada->id)->exists(), 409, 'La compra requiere conciliación antes de continuar.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($recibir) {
                // Guarda en $lineas el resultado de la consulta ejecutada.
                $lineas = $bloqueada->details()->orderBy('id')->lockForUpdate()->get();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($lineas->isEmpty() || $lineas->pluck('producto_id')->unique()->count() !== $lineas->count(), 409, 'Los detalles de compra requieren conciliación.');
                // Guarda en $recibidas el resultado necesario para continuar la operacion.
                $recibidas = $this->cantidadesRecibidasPorDetalle($bloqueada, $lineas);
                // Prepara en $productos la consulta que obtiene o modifica los datos necesarios.
                $productos = Producto::query()->whereIn('id', $lineas->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                // Recorre los elementos recibidos para validar o procesar cada registro.
                foreach ($lineas as $linea) {
                    // Guarda en $producto el resultado de la consulta ejecutada.
                    $producto = $productos->get((int) $linea->producto_id);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($producto instanceof Producto && (int) $producto->empresa_id === $ubicacion['empresa_id'] && (int) $linea->cantidad > 0, 409, 'Un producto de la compra requiere conciliación.');
                    // Guarda en $cantidadPendiente el resultado necesario para continuar la operacion.
                    $cantidadPendiente = (int) $linea->cantidad - (int) ($recibidas[$linea->id] ?? 0);
                    // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                    if ($cantidadPendiente > 0) {
                        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                        $this->registrarEntradaDeCompra($bloqueada, $linea, $producto, $cantidadPendiente, null, $actor, []);
                    }
                }
                // Delega esta parte de la operacion al servicio o dependencia correspondiente.
                $this->comprobarRecepcion($bloqueada, true);
            }
            // Guarda en $anterior el resultado necesario para continuar la operacion.
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => $destino]);
            $this->auditoria->record(
                $actor->id,
                $recibir ? 'compra.recibida' : 'compra.aprobada',
                'compras',
                $bloqueada->id,
                ['estado' => $anterior],
                ['estado' => $destino],
                $ip,
                scope: ['empresa_id' => $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id']]
            );

            return $bloqueada;
        }, 3);
    }

    /**
     * Ejecuta la operación comprobarRecepcion definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Parámetro recibido por la operación.
     * @param  mixed  $completa  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function comprobarRecepcion(Compra $compra, bool $completa): void
    {
        // Guarda en $lineas el resultado de la consulta ejecutada.
        $lineas = $compra->details()->get(['id', 'producto_id', 'variante_id', 'cantidad']);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($lineas->isNotEmpty(), 409, 'La compra no tiene detalles para recibir.');
        // Guarda en $recibidas el resultado necesario para continuar la operacion.
        $recibidas = $this->cantidadesRecibidasPorDetalle($compra, $lineas);

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($lineas as $linea) {
            // Guarda en $cantidadRecibida el resultado necesario para continuar la operacion.
            $cantidadRecibida = (int) ($recibidas[$linea->id] ?? 0);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($cantidadRecibida > (int) $linea->cantidad, 409, 'El inventario excede la cantidad comprada.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($completa) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($cantidadRecibida === (int) $linea->cantidad, 409, 'La recepción no coincide con las cantidades compradas.');
            }
        }
    }

    /**
     * @return array<int, int>
     */
    private function cantidadesRecibidasPorDetalle(Compra $compra, Collection $lineas): array
    {
        $lineasPorClave = $lineas->keyBy(fn ($linea): string => (int) $linea->producto_id.':'.($linea->variante_id === null ? 'base' : (int) $linea->variante_id));
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($lineasPorClave->count() !== $lineas->count(), 409, 'La compra tiene detalles duplicados de producto o variante.');

        // Prepara en $movimientos la consulta que obtiene o modifica los datos necesarios.
        $movimientos = DB::table('movimientos_inventario')
            ->where('documento_tipo', 'compra')
            ->where('documento_id', (string) $compra->id)
            ->where('tipo_movimiento', 'entrada')
            ->where('empresa_id', $compra->empresa_id)
            ->where('sede_id', $compra->sede_id)
            ->where('almacen_id', $compra->almacen_id)
            ->get(['producto_id', 'variante_id', 'cantidad']);
        // Agrupa en $cantidadesPorDetalle los datos que se utilizaran en el siguiente paso.
        $cantidadesPorDetalle = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($movimientos as $movimiento) {
            $clave = (int) $movimiento->producto_id.':'.($movimiento->variante_id === null ? 'base' : (int) $movimiento->variante_id);
            // Guarda en $linea el resultado de la consulta ejecutada.
            $linea = $lineasPorClave->get($clave);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($linea !== null, 409, 'Existe una entrada de inventario que no corresponde a un detalle de esta compra.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if((float) $movimiento->cantidad <= 0 || (float) $movimiento->cantidad !== floor((float) $movimiento->cantidad), 409, 'Una entrada de compra requiere conciliación.');
            $cantidadesPorDetalle[$linea->id] = ($cantidadesPorDetalle[$linea->id] ?? 0) + (int) $movimiento->cantidad;
        }

        return $cantidadesPorDetalle;
    }

    /** @param array<int, array{imei:?string,numero_serie:?string}> $identificadores */
    private function registrarEntradaDeCompra(
        Compra $compra,
        object $linea,
        Producto $producto,
        int $cantidad,
        ?string $observaciones,
        User $actor,
        array $identificadores,
    ): void {
        // Prepara en $variantes la consulta que obtiene o modifica los datos necesarios.
        $variantes = DB::table('variantes_productos')->where('producto_id', $producto->id);
        $varianteId = $linea->variante_id === null ? null : (int) $linea->variante_id;
        // Guarda en $controlaSerie el resultado necesario para continuar la operacion.
        $controlaSerie = false;

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ((clone $variantes)->exists()) {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($varianteId !== null, 409, 'La compra requiere conciliar la variante del producto antes de recibir.');
            // Guarda en $variante el resultado de la consulta ejecutada.
            $variante = (clone $variantes)->where('id', $varianteId)->where('estado', 'activo')->first(['controla_serie']);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($variante !== null, 409, 'La variante de compra no existe, no pertenece al producto o está inactiva.');
            // Guarda en $controlaSerie el resultado necesario para continuar la operacion.
            $controlaSerie = (bool) $variante->controla_serie;
        } else {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($varianteId === null, 409, 'La compra referencia una variante que ya no existe.');
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($controlaSerie) {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless(count($identificadores) === $cantidad, 422, 'Registra un IMEI o número de serie por cada unidad serializada recibida.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless(
                Schema::hasColumns('dispositivos', ['producto_id', 'variante_id', 'imei', 'numero_serie', 'detalle_compra_id', 'almacen_actual_id', 'estado', 'fecha_ingreso'])
                    && Schema::hasColumns('movimientos_dispositivos', ['dispositivo_id', 'movimiento_inventario_id', 'estado_anterior', 'estado_nuevo', 'creado_en'])
                    && Schema::hasColumns('detalles_compras_dispositivos', ['detalle_compra_id', 'dispositivo_id']),
                503,
                'El esquema de trazabilidad de IMEI no está disponible.'
            );
        } else {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($identificadores !== [], 422, 'Este producto no requiere IMEI ni número de serie.');
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if(
                Schema::hasTable('dispositivos') && DB::table('dispositivos')->where('producto_id', $producto->id)->where('variante_id', $varianteId)->exists(),
                409,
                'La variante ya tiene equipos serializados; revisa la configuración de control de serie antes de recibir.'
            );
        }

        // Guarda en $notas el resultado necesario para continuar la operacion.
        $notas = 'Ingreso por compra #'.$compra->id.'; detalle #'.$linea->id;
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_string($observaciones) && trim($observaciones) !== '') {
            $notas .= '. '.$observaciones;
        }

        // Guarda en $movimiento el resultado necesario para continuar la operacion.
        $movimiento = $this->inventario->cambiarProductoBloqueadoEnAlmacen(
            $producto,
            $varianteId,
            'entrada',
            $cantidad,
            mb_substr($notas, 0, 500),
            $actor,
            (int) $compra->empresa_id,
            (int) $compra->sede_id,
            (int) $compra->almacen_id,
            'compra',
            (string) $compra->id,
            true,
        );

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $controlaSerie) {
            return;
        }

        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($movimiento !== null, 409, 'No se pudo crear el movimiento de entrada que respalda los dispositivos.');

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($identificadores as $identificador) {
            try {
                // Prepara en $dispositivoId la consulta que obtiene o modifica los datos necesarios.
                $dispositivoId = DB::table('dispositivos')->insertGetId([
                    'producto_id' => $producto->id,
                    'variante_id' => $varianteId,
                    'imei' => $identificador['imei'],
                    'numero_serie' => $identificador['numero_serie'],
                    'detalle_compra_id' => $linea->id,
                    'almacen_actual_id' => $compra->almacen_id,
                    'estado' => 'disponible',
                    'fecha_ingreso' => now(),
                ]);
            } catch (UniqueConstraintViolationException $excepcion) {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort(409, 'Uno de los IMEI o números de serie ya está registrado.');
            }

            DB::table('detalles_compras_dispositivos')->insert([
                'detalle_compra_id' => $linea->id,
                'dispositivo_id' => $dispositivoId,
            ]);
            DB::table('movimientos_dispositivos')->insert([
                'dispositivo_id' => $dispositivoId,
                'movimiento_inventario_id' => $movimiento->id,
                'estado_anterior' => null,
                'estado_nuevo' => 'disponible',
                'creado_en' => now(),
            ]);
        }
    }

    /** @return array<int, array{imei:?string,numero_serie:?string}> */
    private function normalizarIdentificadores(string $texto): array
    {
        // Guarda en $lineas el resultado necesario para continuar la operacion.
        $lineas = preg_split('/\R/u', trim($texto), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        // Agrupa en $identificadores los datos que se utilizaran en el siguiente paso.
        $identificadores = [];
        // Agrupa en $vistos los datos que se utilizaran en el siguiente paso.
        $vistos = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($lineas as $linea) {
            // Guarda en $imei el resultado necesario para continuar la operacion.
            $imei = null;
            // Guarda en $numeroSerie el resultado necesario para continuar la operacion.
            $numeroSerie = null;

            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach (explode('|', trim($linea)) as $parte) {
                // Guarda en $parte el resultado necesario para continuar la operacion.
                $parte = trim($parte);
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if (preg_match('/\AIMEI:([0-9]{15})\z/i', $parte, $coincidencia) === 1 && $imei === null) {
                    // Guarda en $imei el resultado necesario para continuar la operacion.
                    $imei = $coincidencia[1];
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($this->esImeiValido($imei), 422, 'El IMEI debe tener 15 dígitos y un dígito de control válido.');
                } elseif (preg_match('/\ASERIE:(.{1,100})\z/iu', $parte, $coincidencia) === 1 && $numeroSerie === null) {
                    // Guarda en $numeroSerie el resultado necesario para continuar la operacion.
                    $numeroSerie = trim($coincidencia[1]);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_if($numeroSerie === '', 422, 'El nmero de serie no puede estar vacío.');
                } else {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort(422, 'Usa una linea por unidad con formato IMEI:15dígitos, SERIE:valor o IMEI:15dígitos|SERIE:valor.');
                }
            }

            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($imei === null && $numeroSerie === null, 422, 'Cada unidad requiere un IMEI o número de serie.');
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ([['imei', $imei], ['serie', $numeroSerie]] as [$tipo, $valor]) {
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($valor === null) {
                    continue;
                }
                // Guarda en $clave el resultado necesario para continuar la operacion.
                $clave = $tipo.':'.mb_strtolower($valor);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(isset($vistos[$clave]), 422, 'No repitas un IMEI o número de serie en la recepción.');
                $vistos[$clave] = true;
            }
            $identificadores[] = ['imei' => $imei, 'numero_serie' => $numeroSerie];
        }

        return $identificadores;
    }

    /**
     * Ejecuta la operación esImeiValido definida por la clase ServicioCompras.
     *
     * @param  mixed  $imei  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function esImeiValido(string $imei): bool
    {
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (preg_match('/\A[0-9]{15}\z/', $imei) !== 1) {
            return false;
        }

        // Guarda en $suma el resultado necesario para continuar la operacion.
        $suma = 0;
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach (str_split(substr($imei, 0, 14)) as $indice => $digito) {
            // Guarda en $valor el resultado necesario para continuar la operacion.
            $valor = (int) $digito;
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($indice % 2 === 1) {
                $valor *= 2;
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($valor > 9) {
                    $valor -= 9;
                }
            }
            $suma += $valor;
        }

        return (10 - ($suma % 10)) % 10 === (int) substr($imei, 14, 1);
    }

    /**
     * Ejecuta la operación centimos definida por la clase ServicioCompras.
     *
     * @param  mixed  $valor  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function centimos(string $valor): int
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $valor) === 1, 422, 'El costo unitario no es válido.');
        [$entero, $decimal] = array_pad(explode('.', $valor, 2), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
    }

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $centimos  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function importe(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }
}
