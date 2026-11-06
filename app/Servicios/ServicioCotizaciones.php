<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Comercio\DetallePedido;
use App\Models\Comercio\DetalleVenta;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioCotizaciones dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioCotizaciones
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $authorization,
        private readonly RegistradorAuditoria $auditoria,
    ) {}

    /**
     * Crea una cotización para el usuario autorizado y registra su auditoría.
     *
     * Determina el cliente mayorista, valida propiedad o alcance del empleado, bloquea
     * los productos de la empresa, calcula el total y persiste cabecera y detalles en
     * una transacción.
     *
     * @param array<string, mixed> $data Datos validados de la cotización y sus detalles.
     * @param User $user Usuario que crea la cotización.
     * @return Cotizacion Cotización persistida.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si falla autorización, validación o alcance empresarial.
     */
    public function create(array $data, User $user): Cotizacion
    {
        $this->authorization->assertAllowed($user, 'gestion.cotizaciones.crear');

        return DB::transaction(function () use ($data, $user): Cotizacion {
            $customerId = $data['cliente_id'] ?? Cliente::whereBelongsTo($user, 'user')->where('tipo', 'mayorista')->value('id');
            abort_unless($customerId, 422, 'El usuario debe tener un perfil de cliente mayorista.');

            $customer = Cliente::findOrFail($customerId);
            $isOwner = (int) $customer->usuario_id === (int) $user->id
                && $this->authorization->hasRole($user, 'cliente_mayorista');
            $isAuthorizedEmployee = ($this->authorization->hasRole($user, 'administrador') || $this->authorization->hasRole($user, 'ventas_mayoristas'))
                && $this->authorization->allowsRecord(
                    $user,
                    'gestion.cotizaciones.crear',
                    is_numeric($customer->empresa_id) ? (int) $customer->empresa_id : null,
                    $this->authorization->activeSiteIdFor($user),
                );
            abort_unless($isOwner || $isAuthorizedEmployee, 403);

            $items = collect($data['items']);
            $products = Producto::query()->whereIn('id', $items->pluck('producto_id'))
                ->where('empresa_id', $customer->empresa_id)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $total = 0.0;
            $lines = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['producto_id']);
                abort_unless($product instanceof Producto && $product->activo, 422, 'Producto no disponible.');
                $price = (float) $product->effective_price;
                $total += $price * (int) $item['cantidad'];
                $lines[] = [$product->id, (int) $item['cantidad'], $price];
            }

            $quotation = Cotizacion::create([
                'cliente_id' => $customer->id,
                'creado_por_usuario_id' => $user->id,
                'total' => $total,
                'estado' => 'Borrador',
                'notas' => $data['notas'] ?? null,
                'activo' => true,
                'creado_en' => now(),
                ...$this->authorization->operationScopeAttributes($user, 'cotizaciones'),
            ]);

            foreach ($lines as [$productId, $quantity, $price]) {
                $quotation->details()->create(['producto_id' => $productId, 'cantidad' => $quantity, 'precio_unitario' => $price]);
            }

            return $quotation;
        }, 3);
    }

    /**
     * Ejecuta la operación aprobar definida por la clase ServicioCotizaciones.
     *
     * @param  mixed  $cotizacion  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Cotizacion Resultado de la operación.
     */
    public function aprobar(Cotizacion $cotizacion, User $usuario, ?string $ip): Cotizacion
    {
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.aprobar');

        return DB::transaction(function () use ($cotizacion, $usuario, $ip): Cotizacion {
            $cotizacionBloqueada = Cotizacion::query()->whereKey($cotizacion->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($cotizacionBloqueada->activo, 409, 'La cotizacion esta desactivada.');
            abort_unless($this->authorization->allowsRecord(
                $usuario,
                'gestion.cotizaciones.aprobar',
                is_numeric($cotizacionBloqueada->empresa_id) ? (int) $cotizacionBloqueada->empresa_id : null,
                is_numeric($cotizacionBloqueada->sede_id) ? (int) $cotizacionBloqueada->sede_id : null,
            ), 403);
            abort_unless(in_array($cotizacionBloqueada->estado, ['Pendiente', 'Enviada'], true), 409, 'Solo se pueden aprobar cotizaciones pendientes o enviadas.');
            abort_unless($cotizacionBloqueada->cliente_id !== null
                && $cotizacionBloqueada->customer()->where('activo', true)->where('empresa_id', $cotizacionBloqueada->empresa_id)->exists(), 409, 'La cotizacion requiere un cliente activo de la empresa correspondiente.');
            abort_unless($cotizacionBloqueada->details()->exists(), 409, 'La cotizacion no contiene productos para aprobar.');
            abort_if(DB::table('pedidos')->where('cotizacion_id', $cotizacionBloqueada->id)->exists(), 409, 'La cotizacion ya fue convertida en pedido.');

            $estadoAnterior = $cotizacionBloqueada->estado;
            $cotizacionBloqueada->update(['estado' => 'Aprobada']);
            $this->auditoria->record($usuario->id, 'cotizacion.aprobada', 'cotizaciones', $cotizacionBloqueada->id,
                ['estado' => $estadoAnterior], ['estado' => 'Aprobada'], $ip,
                scope: ['empresa_id' => (int) $cotizacionBloqueada->empresa_id, 'sede_id' => (int) $cotizacionBloqueada->sede_id]);

            return $cotizacionBloqueada;
        }, 3);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $cotizacion  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Cotizacion Resultado de la operación.
     */
    public function registrarEnvioManual(Cotizacion $cotizacion, User $usuario, ?string $ip): Cotizacion
    {
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.actualizar');

        return DB::transaction(function () use ($cotizacion, $usuario, $ip): Cotizacion {
            $cotizacionBloqueada = Cotizacion::query()->whereKey($cotizacion->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($cotizacionBloqueada->activo, 409, 'La cotizacion esta desactivada.');
            abort_unless($this->authorization->allowsRecord(
                $usuario,
                'gestion.cotizaciones.actualizar',
                is_numeric($cotizacionBloqueada->empresa_id) ? (int) $cotizacionBloqueada->empresa_id : null,
                is_numeric($cotizacionBloqueada->sede_id) ? (int) $cotizacionBloqueada->sede_id : null,
            ), 403);
            if ($cotizacionBloqueada->estado === 'Enviada') {
                return $cotizacionBloqueada;
            }
            abort_unless(in_array($cotizacionBloqueada->estado, ['Borrador', 'Pendiente'], true), 409, 'El estado actual no permite registrar el envio manual.');

            $cliente = $cotizacionBloqueada->customer()->where('activo', true)
                ->where('empresa_id', $cotizacionBloqueada->empresa_id)->first();
            abort_unless($cliente !== null && filter_var($cliente->correo, FILTER_VALIDATE_EMAIL) !== false, 409, 'La cotizacion requiere un correo valido para el cliente de la empresa.');
            abort_unless($cotizacionBloqueada->details()->exists(), 409, 'La cotizacion no contiene productos para enviar.');

            $estadoAnterior = $cotizacionBloqueada->estado;
            $cotizacionBloqueada->update(['estado' => 'Enviada']);
            $this->auditoria->record($usuario->id, 'cotizacion.envio_manual_registrado', 'cotizaciones', $cotizacionBloqueada->id,
                ['estado' => $estadoAnterior], ['estado' => 'Enviada', 'destinatario' => $cliente->correo, 'canal' => 'correo manual'], $ip,
                observations: 'El operador confirmo que envio el PDF descargado por un canal de correo externo.',
                scope: ['empresa_id' => (int) $cotizacionBloqueada->empresa_id, 'sede_id' => (int) $cotizacionBloqueada->sede_id]);

            return $cotizacionBloqueada;
        }, 3);
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $cotizacion  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Cotizacion Resultado de la operación.
     */
    public function desactivar(Cotizacion $cotizacion, User $usuario, ?string $ip): Cotizacion
    {
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.actualizar');

        return DB::transaction(function () use ($cotizacion, $usuario, $ip): Cotizacion {
            $bloqueada = Cotizacion::query()->whereKey($cotizacion->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->authorization->allowsRecord(
                $usuario,
                'gestion.cotizaciones.actualizar',
                is_numeric($bloqueada->empresa_id) ? (int) $bloqueada->empresa_id : null,
                is_numeric($bloqueada->sede_id) ? (int) $bloqueada->sede_id : null,
            ), 403);
            abort_unless($bloqueada->activo, 409, 'La cotizacion ya esta desactivada.');
            abort_unless(in_array($bloqueada->estado, ['Borrador', 'Pendiente'], true), 409, 'Solo se pueden desactivar cotizaciones en borrador o pendientes.');
            $bloqueada->update(['activo' => false]);
            $this->auditoria->record($usuario->id, 'cotizacion.desactivada', 'cotizaciones', $bloqueada->id,
                ['activo' => true, 'estado' => $bloqueada->estado], ['activo' => false, 'estado' => $bloqueada->estado], $ip,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id]);

            return $bloqueada;
        }, 3);
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $cotizacion  Parámetro recibido por la operación.
     * @param  mixed  $data  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Cotizacion Resultado de la operación.
     */
    public function actualizar(Cotizacion $cotizacion, array $data, User $usuario, ?string $ip): Cotizacion
    {
        $this->authorization->assertAllowed($usuario, 'gestion.cotizaciones.actualizar');

        return DB::transaction(function () use ($cotizacion, $data, $usuario, $ip): Cotizacion {
            $bloqueada = Cotizacion::query()->whereKey($cotizacion->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($bloqueada->activo, 409, 'La cotización está desactivada.');
            abort_unless(in_array($bloqueada->estado, ['Borrador', 'Pendiente'], true), 409, 'Solo se pueden editar cotizaciones en borrador o pendientes.');
            abort_unless($this->authorization->allowsRecord(
                $usuario,
                'gestion.cotizaciones.actualizar',
                is_numeric($bloqueada->empresa_id) ? (int) $bloqueada->empresa_id : null,
                is_numeric($bloqueada->sede_id) ? (int) $bloqueada->sede_id : null,
            ), 403);

            $cliente = Cliente::query()->whereKey((int) $data['cliente_id'])
                ->where('empresa_id', $bloqueada->empresa_id)->where('tipo', 'mayorista')->where('activo', true)->first();
            abort_unless($cliente !== null, 422, 'El cliente debe ser mayorista, activo y pertenecer a la empresa de la cotización.');

            $items = collect($data['items']);
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id')->unique())
                ->where('empresa_id', $bloqueada->empresa_id)->where('activo', true)->lockForUpdate()->get()->keyBy('id');
            $total = 0.0;
            $lineas = [];
            foreach ($items as $item) {
                $producto = $productos->get((int) $item['producto_id']);
                abort_unless($producto instanceof Producto, 422, 'Uno de los productos no pertenece a la empresa o está inactivo.');
                $cantidad = (int) $item['cantidad'];
                $precio = (float) $producto->effective_price;
                $total += $precio * $cantidad;
                $lineas[] = [$producto->id, $cantidad, $precio];
            }

            $antes = ['cliente_id' => $bloqueada->cliente_id, 'total' => $bloqueada->total, 'notas' => $bloqueada->notas, 'detalle_ids' => $bloqueada->details()->pluck('id')->all()];
            $bloqueada->update(['cliente_id' => $cliente->id, 'total' => $total, 'notas' => $data['notas'] ?? null]);
            $bloqueada->details()->delete();
            foreach ($lineas as [$productoId, $cantidad, $precio]) {
                $bloqueada->details()->create(['producto_id' => $productoId, 'cantidad' => $cantidad, 'precio_unitario' => $precio]);
            }
            $this->auditoria->record($usuario->id, 'cotizacion.actualizada', 'cotizaciones', $bloqueada->id, $antes,
                ['cliente_id' => $cliente->id, 'total' => $total, 'notas' => $data['notas'] ?? null, 'lineas' => count($lineas)], $ip,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id]);

            return $bloqueada->fresh(['customer', 'details.product']);
        }, 3);
    }

    /**
     * Ejecuta la operación convertirAventa definida por la clase ServicioCotizaciones.
     *
     * @param  mixed  $cotizacion  Parámetro recibido por la operación.
     * @param  mixed  $almacenId  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Venta Resultado de la operación.
     */
    public function convertirAventa(Cotizacion $cotizacion, int $almacenId, User $usuario, ?string $ip): Venta
    {
        $this->authorization->assertAllowed($usuario, 'gestion.ventas.crear');

        return DB::transaction(function () use ($cotizacion, $almacenId, $usuario, $ip): Venta {
            $cotizacionBloqueada = Cotizacion::query()->whereKey($cotizacion->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($cotizacionBloqueada->activo, 409, 'La cotizacion esta desactivada.');
            abort_unless($this->authorization->allowsRecord(
                $usuario,
                'gestion.ventas.crear',
                is_numeric($cotizacionBloqueada->empresa_id) ? (int) $cotizacionBloqueada->empresa_id : null,
                is_numeric($cotizacionBloqueada->sede_id) ? (int) $cotizacionBloqueada->sede_id : null,
            ), 403);

            $pedidoExistente = Pedido::query()->where('cotizacion_id', $cotizacionBloqueada->id)->lockForUpdate()->first();
            if ($pedidoExistente !== null) {
                $ventaExistente = Venta::query()->where('pedido_id', $pedidoExistente->id)->lockForUpdate()->first();
                abort_unless($cotizacionBloqueada->estado === 'Convertida' && $ventaExistente !== null, 409, 'La cotizacion ya tiene un pedido asociado que requiere conciliacion.');

                return $ventaExistente;
            }

            abort_unless($cotizacionBloqueada->estado === 'Aprobada', 409, 'Solo se pueden convertir cotizaciones aprobadas.');
            abort_unless(Schema::hasColumns('ventas', ['canal', 'observaciones', 'pedido_id'])
                && Schema::hasColumns('pedidos', ['cotizacion_id', 'canal', 'tipo_venta', 'empresa_id', 'sede_id', 'almacen_id'])
                && Schema::hasColumns('detalles_pedidos', ['variante_id', 'descuento', 'impuesto'])
                && Schema::hasColumns('detalles_cotizaciones', ['variante_id', 'descuento', 'impuesto', 'total_linea'])
                && Schema::hasColumns('detalles_ventas', ['detalle_pedido_id', 'variante_id']),
                503, 'El esquema de ventas vinculadas no contiene las columnas requeridas.');

            $almacen = DB::table('almacenes')
                ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
                ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
                ->where('almacenes.id', $almacenId)
                ->where('almacenes.sede_id', $cotizacionBloqueada->sede_id)
                ->where('sedes.empresa_id', $cotizacionBloqueada->empresa_id)
                ->where('almacenes.activo', true)
                ->where('almacenes.permite_venta', true)
                ->where('sedes.activa', true)
                ->where('empresas.activo', true)
                ->lockForUpdate()
                ->first(['almacenes.id', 'almacenes.sede_id', 'sedes.empresa_id']);
            abort_unless($almacen !== null, 422, 'Selecciona un almacen activo y autorizado para ventas en la sede de la cotizacion.');
            abort_unless($this->authorization->allowsRecord($usuario, 'gestion.ventas.crear', (int) $almacen->empresa_id, (int) $almacen->sede_id), 403);

            $cliente = Cliente::query()->whereKey($cotizacionBloqueada->cliente_id)
                ->where('empresa_id', $cotizacionBloqueada->empresa_id)->where('activo', true)->first();
            abort_unless($cliente !== null, 409, 'La cotizacion requiere un cliente activo de la misma empresa.');
            $detalles = $cotizacionBloqueada->details()->orderBy('id')->lockForUpdate()->get();
            abort_unless($detalles->isNotEmpty(), 409, 'La cotizacion aprobada no contiene productos.');

            $subcentimos = 0;
            $descuentosCentimos = 0;
            $impuestosCentimos = 0;
            $totalCentimos = 0;
            $lineas = [];
            $productos = Producto::query()->whereIn('id', $detalles->pluck('producto_id')->unique())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($detalles as $detalle) {
                $producto = $productos->get((int) $detalle->producto_id);
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === (int) $cotizacionBloqueada->empresa_id, 409, 'Un producto de la cotizacion no esta activo en la empresa autorizada.');
                abort_unless((int) $detalle->cantidad > 0 && (int) $detalle->cantidad <= 1000, 409, 'La cotizacion contiene una cantidad no valida para el flujo actual de ventas.');
                $varianteId = $detalle->variante_id === null ? null : (int) $detalle->variante_id;
                $variante = null;
                if ($varianteId !== null) {
                    $variante = DB::table('variantes_productos')->where('id', $varianteId)->where('producto_id', $producto->id)->where('estado', 'activo')->lockForUpdate()->first(['id', 'codigo_sku', 'controla_serie']);
                    abort_unless($variante !== null, 409, 'Una variante de la cotizacion ya no esta activa o no corresponde al producto.');
                }
                $unitario = (int) round((float) $detalle->precio_unitario * 100);
                $descuento = (int) round((float) $detalle->descuento * 100);
                $impuesto = (int) round((float) $detalle->impuesto * 100);
                $baseLinea = $unitario * (int) $detalle->cantidad;
                $totalLinea = $detalle->total_linea === null ? $baseLinea - $descuento + $impuesto : (int) round((float) $detalle->total_linea * 100);
                abort_if($unitario < 0 || $descuento < 0 || $descuento > $baseLinea || $impuesto < 0
                    || $totalLinea < 0 || $totalLinea !== $baseLinea - $descuento + $impuesto,
                    409, 'Una linea de la cotizacion contiene importes no validos o requiere conciliacion.');
                $subcentimos += $baseLinea;
                $descuentosCentimos += $descuento;
                $impuestosCentimos += $impuesto;
                $totalCentimos += $totalLinea;
                $lineas[] = [$detalle, $producto, $variante, $baseLinea, $descuento, $impuesto, $totalLinea];
            }

            abort_unless($totalCentimos === (int) round((float) $cotizacionBloqueada->total * 100), 409, 'El total aprobado no coincide con el detalle de la cotizacion; requiere conciliacion.');
            abort_if($totalCentimos > 999999999, 422, 'El total supera la capacidad del pedido comercial.');

            $pedido = Pedido::query()->create([
                'usuario_id' => $usuario->id,
                'cliente_id' => $cliente->id,
                'cotizacion_id' => $cotizacionBloqueada->id,
                'canal' => 'presencial',
                'tipo_venta' => 'fisica',
                'observaciones' => $cotizacionBloqueada->notas,
                'subtotal' => $this->importeCotizacion($subcentimos),
                'descuento' => $this->importeCotizacion($descuentosCentimos),
                'total' => $this->importeCotizacion($totalCentimos),
                'estado' => 'Pendiente',
                'creado_en' => now(),
                'empresa_id' => $almacen->empresa_id,
                'sede_id' => $almacen->sede_id,
                'almacen_id' => $almacen->id,
            ]);

            $venta = Venta::query()->create([
                'empresa_id' => $almacen->empresa_id,
                'sede_id' => $almacen->sede_id,
                'almacen_id' => $almacen->id,
                'pedido_id' => $pedido->id,
                'cliente_id' => $cliente->id,
                'usuario_id' => $usuario->id,
                'codigo' => 'COT-'.$cotizacionBloqueada->id,
                'estado' => 'borrador',
                'moneda' => $cotizacionBloqueada->moneda,
                'subtotal' => $this->importeCotizacion($subcentimos),
                'descuento' => $this->importeCotizacion($descuentosCentimos),
                'impuestos' => $this->importeCotizacion($impuestosCentimos),
                'total' => $this->importeCotizacion($totalCentimos),
                'fecha_venta' => now(),
                'canal' => 'presencial',
                'observaciones' => $cotizacionBloqueada->notas,
            ]);

            foreach ($lineas as [$detalle, $producto, $variante, $baseLinea, $descuento, $impuesto, $totalLinea]) {
                $detallePedido = DetallePedido::query()->create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'variante_id' => $variante?->id,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descripcion_historica' => $detalle->descripcion_historica ?: $producto->nombre,
                    'descuento' => $detalle->descuento,
                    'impuesto' => $detalle->impuesto,
                ]);
                DetalleVenta::query()->create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'variante_id' => $variante?->id,
                    'detalle_pedido_id' => $detallePedido->id,
                    'descripcion_historica' => $detallePedido->descripcion_historica,
                    'sku_historico' => $variante?->codigo_sku,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descuento' => $this->importeCotizacion($descuento),
                    'impuesto' => $this->importeCotizacion($impuesto),
                    'total_linea' => $this->importeCotizacion($totalLinea),
                ]);
            }

            $cotizacionBloqueada->update(['estado' => 'Convertida']);
            $this->auditoria->record($usuario->id, 'cotizacion.convertida_en_venta_borrador', 'cotizaciones', $cotizacionBloqueada->id,
                ['estado' => 'Aprobada'], ['estado' => 'Convertida', 'pedido_id' => $pedido->id, 'venta_id' => $venta->id, 'estado_venta' => 'borrador'], $ip,
                scope: ['empresa_id' => (int) $almacen->empresa_id, 'sede_id' => (int) $almacen->sede_id]);

            return $venta->load('detalles');
        }, 3);
    }

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $centimos  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function importeCotizacion(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }
}
