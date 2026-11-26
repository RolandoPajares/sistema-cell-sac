<?php

namespace App\Servicios\Compartidos\Comercio;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Cupon;
use App\Models\Comercio\EnvioPedido;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use App\Servicios\Publico\Comercio\ServicioCarrito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioPedidos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioPedidos
{
    private const MAX_ITEM_QUANTITY = 1000;

    private const MAX_ORDER_TOTAL_CENTS = 9999999999;

    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioInventario $inventoryService,
        private readonly RegistradorAuditoria $auditLogger,
        private readonly ServicioAutorizacion $authorization,
        private readonly ServicioCarrito $cartService,
    ) {}

    /**
     * @param  array<int|string, mixed>  $cart
     */
    public function finalizarCompra(User $user, array $cart, ?string $couponCode, ?string $ip, string $checkoutToken): Pedido
    {
        // Valida carrito, calcula totales y crea el pedido de un usuario autenticado.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $user->can('gestion.cotizaciones.consultar') && ! $user->can('gestion.pedidos.consultar')) {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless(
                $this->authorization->allows($user, 'gestion.pedidos.crear')
                    || $this->authorization->allows($user, 'gestion.pedidos.consultar'),
                403,
            );
        }
        // Guarda en $cart el resultado necesario para continuar la operacion.
        $cart = $this->cartService->normalizar($cart);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($cart === [] || preg_match('/^[a-f0-9]{64}$/', $checkoutToken) !== 1, 422, 'La solicitud de compra no es válida.');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        return DB::transaction(function () use ($user, $cart, $couponCode, $ip, $checkoutToken): Pedido {
            // Guarda en $location el resultado necesario para continuar la operacion.
            $location = $this->authorization->operationLocation($user);
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null && $location['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
            // Guarda en $tokenHash el resultado necesario para continuar la operacion.
            $tokenHash = hash('sha256', $user->getKey().':'.$checkoutToken);
            DB::table('carritos')->upsert([[
                'empresa_id' => $location['empresa_id'],
                'sede_id' => $location['sede_id'],
                'usuario_id' => $user->getKey(),
                'resumen_token' => $tokenHash,
                'estado' => 'activo',
            ]], ['resumen_token'], ['resumen_token']);
            // Prepara en $checkout la consulta que obtiene o modifica los datos necesarios.
            $checkout = DB::table('carritos')->where('resumen_token', $tokenHash)->lockForUpdate()->first();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($checkout !== null && (int) $checkout->usuario_id === (int) $user->getKey()
                && (int) $checkout->empresa_id === $location['empresa_id'] && (int) $checkout->sede_id === $location['sede_id'], 409, 'La solicitud de compra pertenece a otro contexto.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($checkout->pedido_id !== null) {
                // Devuelve el resultado obtenido mediante el modelo correspondiente.
                return Pedido::query()->findOrFail($checkout->pedido_id);
            }
            // Prepara en $products la consulta que obtiene o modifica los datos necesarios.
            $products = Producto::query()
                ->whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            // Agrupa en $items los datos que se utilizaran en el siguiente paso.
            $items = [];
            // Guarda en $subtotalCents el resultado necesario para continuar la operacion.
            $subtotalCents = 0;

            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($cart as $id => $linea) {
                // Guarda en $quantity el resultado necesario para continuar la operacion.
                $quantity = $linea['cantidad'];
                // Guarda en $varianteId el resultado necesario para continuar la operacion.
                $varianteId = $linea['variante_id'];
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($quantity === false || $quantity < 1 || $quantity > self::MAX_ITEM_QUANTITY, 422, 'Cantidad de carrito inválida.');

                // Guarda en $product el resultado de la consulta ejecutada.
                $product = $products->get((int) $id);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($product instanceof Producto && $product->activo, 422, 'Un producto dejó de estar disponible.');
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($varianteId !== null) {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($product->variants()->whereKey($varianteId)->where('estado', 'activo')->exists(), 422, 'Esta combinación no está disponible.');
                } else {
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless(! $product->variants()->exists(), 422, 'Selecciona una variante.');
                }
                // Guarda en $disponible el resultado necesario para continuar la operacion.
                $disponible = $this->inventoryService->disponibilidadDeLinea($user, $product, $varianteId);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($quantity > $disponible, 422, 'Stock insuficiente en el almacén autorizado para '.$product->nombre.'.');

                // Guarda en $priceCents el resultado necesario para continuar la operacion.
                $priceCents = (int) round((float) $product->effective_price * 100, 0, PHP_ROUND_HALF_UP);
                // Guarda en $lineCents el resultado necesario para continuar la operacion.
                $lineCents = $priceCents * $quantity;
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($lineCents < 0 || $subtotalCents > self::MAX_ORDER_TOTAL_CENTS - $lineCents, 422, 'El pedido supera el total permitido.');

                $subtotalCents += $lineCents;
                $items[] = [$product, $varianteId, $quantity, $priceCents];
            }

            // Guarda en $descuentoCupon el resultado necesario para continuar la operacion.
            $descuentoCupon = $this->descuentoPara($couponCode, $subtotalCents, (int) $location['empresa_id']);
            // Crea y guarda en $order el registro gestionado por este servicio.
            $order = Pedido::create([
                'usuario_id' => $user->id,
                'canal' => 'ecommerce',
                'subtotal' => $subtotalCents / 100,
                'descuento' => $descuentoCupon['descuento_centimos'] / 100,
                'cupon_id' => $descuentoCupon['cupon_id'],
                'cupon_codigo' => $couponCode,
                'total' => ($subtotalCents - $descuentoCupon['descuento_centimos']) / 100,
                'estado' => 'Pendiente',
                'creado_en' => now(),
                ...$this->authorization->operationScopeAttributes($user, 'pedidos'),
            ]);
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->asegurarEnvio($order, $location, $user->getKey());

            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($items as [$product, $varianteId, $quantity, $priceCents]) {
                // Guarda en $movimiento el resultado necesario para continuar la operacion.
                $movimiento = $this->inventoryService->cambiarProductoBloqueadoEnAlmacen(
                    $product,
                    $varianteId,
                    'salida',
                    $quantity,
                    'Salida por pedido #'.$order->id,
                    $user,
                    (int) $location['empresa_id'],
                    (int) $location['sede_id'],
                    (int) $location['almacen_id'],
                    'pedido',
                    (string) $order->id,
                );
                $order->details()->create([
                    'producto_id' => $product->id,
                    'variante_id' => $movimiento->variante_id,
                    'cantidad' => $quantity,
                    'precio_unitario' => $priceCents / 100,
                ]);
            }

            DB::table('carritos')->where('id', $checkout->id)->update([
                'pedido_id' => $order->id,
                'estado' => 'convertido',
                'actualizado_en' => now(),
            ]);

            $this->auditLogger->record($user->id, 'order.created', 'pedidos', $order->id, null, ['total' => $order->total, 'items' => count($items)], $ip);

            return $order;
        }, 3);
    }

    /**
     * Registra un pedido de visitante con la misma validación transaccional del checkout autenticado.
     *
     * @param  array{nombre:string,correo:string,telefono?:string|null,direccion:string,referencia?:string|null}  $guest
     */
    public function finalizarCompraInvitado(array $cart, ?string $couponCode, ?string $ip, array $guest): Pedido
    {
        // Crea el pedido de un invitado guardando sus datos y token de seguimiento.
        // Guarda en $cart el resultado necesario para continuar la operacion.
        $cart = $this->cartService->normalizar($cart);
        // Guarda en $checkoutToken el resultado de la consulta ejecutada.
        $checkoutToken = (string) request()->session()->get('checkout_token');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($cart === [] || preg_match('/^[a-f0-9]{64}$/', $checkoutToken) !== 1, 422, 'La solicitud de compra no es válida.');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($cart, $couponCode, $ip, $guest, $checkoutToken): Pedido {
            // Guarda en $location el resultado necesario para continuar la operacion.
            $location = $this->inventoryService->ubicacionPublica();
            // Guarda en $tokenHash el resultado necesario para continuar la operacion.
            $tokenHash = hash('sha256', 'guest:'.$checkoutToken);
            DB::table('carritos')->upsert([[
                'empresa_id' => $location['empresa_id'], 'sede_id' => $location['sede_id'],
                'usuario_id' => null, 'resumen_token' => $tokenHash, 'estado' => 'activo',
            ]], ['resumen_token'], ['resumen_token']);
            // Prepara en $checkout la consulta que obtiene o modifica los datos necesarios.
            $checkout = DB::table('carritos')->where('resumen_token', $tokenHash)->lockForUpdate()->first();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($checkout !== null, 409, 'La sesión de compra no es válida.');
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($checkout->pedido_id !== null) {
                // Devuelve el resultado obtenido mediante el modelo correspondiente.
                return Pedido::query()->findOrFail($checkout->pedido_id);
            }

            // Prepara en $products la consulta que obtiene o modifica los datos necesarios.
            $products = Producto::query()->whereIn('id', array_keys($cart))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            // Agrupa en $items los datos que se utilizaran en el siguiente paso.
            $items = [];
            // Guarda en $subtotalCents el resultado necesario para continuar la operacion.
            $subtotalCents = 0;
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($cart as $id => $linea) {
                // Guarda en $product el resultado de la consulta ejecutada.
                $product = $products->get((int) $id);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($product instanceof Producto && $product->activo, 422, 'Un producto dejó de estar disponible.');
                // Guarda en $variantId el resultado necesario para continuar la operacion.
                $variantId = $linea['variante_id'];
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($variantId === null ? ! $product->variants()->exists() : $product->variants()->whereKey($variantId)->where('estado', 'activo')->exists(), 422, 'La variante no está disponible.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($linea['cantidad'] > $this->inventoryService->disponibilidadPublica($product, $variantId), 422, 'Stock insuficiente.');
                // Guarda en $priceCents el resultado necesario para continuar la operacion.
                $priceCents = (int) round((float) $product->effective_price * 100, 0, PHP_ROUND_HALF_UP);
                $subtotalCents += $priceCents * $linea['cantidad'];
                $items[] = [$product, $variantId, $linea['cantidad'], $priceCents];
            }
            // Guarda en $discount el resultado necesario para continuar la operacion.
            $discount = $this->descuentoPara($couponCode, $subtotalCents, $location['empresa_id']);
            // Crea y guarda en $order el registro gestionado por este servicio.
            $order = Pedido::create([
                'usuario_id' => null, 'cliente_invitado_nombre' => $guest['nombre'], 'cliente_invitado_correo' => $guest['correo'],
                'cliente_invitado_telefono' => $guest['telefono'] ?? null, 'token_acceso_publico' => $tokenHash,
                'direccion_entrega_snapshot' => ['direccion' => $guest['direccion'], 'referencia' => $guest['referencia'] ?? null],
                'canal' => 'ecommerce', 'subtotal' => $subtotalCents / 100, 'descuento' => $discount['descuento_centimos'] / 100,
                'cupon_id' => $discount['cupon_id'], 'cupon_codigo' => $couponCode,
                'total' => ($subtotalCents - $discount['descuento_centimos']) / 100, 'estado' => 'Pendiente', 'creado_en' => now(), ...$location,
            ]);
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->asegurarEnvio($order, $location, null);
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($items as [$product, $variantId, $quantity, $priceCents]) {
                // Guarda en $movement el resultado necesario para continuar la operacion.
                $movement = $this->inventoryService->cambiarProductoBloqueadoEnAlmacenPublico($product, $variantId, 'salida', $quantity, 'Salida por pedido #'.$order->id, 'pedido', (string) $order->id);
                $order->details()->create(['producto_id' => $product->id, 'variante_id' => $movement->variante_id, 'cantidad' => $quantity, 'precio_unitario' => $priceCents / 100]);
            }
            DB::table('carritos')->where('id', $checkout->id)->update(['pedido_id' => $order->id, 'estado' => 'convertido', 'actualizado_en' => now()]);
            $this->auditLogger->record(null, 'order.created.guest', 'pedidos', $order->id, null, ['total' => $order->total], $ip, scope: $location);

            return $order;
        }, 3);
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $order  Parámetro recibido por la operación.
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $status  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function actualizarEstado(Pedido $order, User $user, string $status, ?string $ip): void
    {
        // Cambia el estado despues de validar el permiso del usuario.
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($this->authorization->allowsRecord(
            $user,
            'gestion.pedidos.actualizar',
            is_numeric($order->empresa_id) ? (int) $order->empresa_id : null,
            is_numeric($order->sede_id) ? (int) $order->sede_id : null,
        ), 403);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        DB::transaction(function () use ($order, $user, $status, $ip): void {
            // Guarda en $lockedOrder el resultado necesario para continuar la operacion.
            $lockedOrder = Pedido::with('details')->whereKey($order->id)->lockForUpdate()->firstOrFail();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if($lockedOrder->estado === 'Cancelado' && $status !== 'Cancelado', 422, 'Un pedido cancelado no puede reabrirse.');

            // Guarda en $before el resultado necesario para continuar la operacion.
            $before = $lockedOrder->estado;
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($status === 'Cancelado' && $before !== 'Cancelado') {
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(in_array($before, ['Enviado', 'Entregado'], true), 409, 'El pedido ya fue enviado o entregado y requiere un proceso de devolución.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(Schema::hasTable('pagos') && DB::table('pagos')->where('pedido_id', $lockedOrder->id)->exists(), 409, 'El pedido tiene registros de pago que requieren conciliación.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(Schema::hasTable('ventas') && DB::table('ventas')->where('pedido_id', $lockedOrder->id)->exists(), 409, 'El pedido está vinculado a una venta.');
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if(Schema::hasTable('devoluciones_garantias') && DB::table('devoluciones_garantias')->where('pedido_id', $lockedOrder->id)->exists(), 409, 'El pedido tiene una devolución o garantía abierta.');
                // Guarda en $location el resultado necesario para continuar la operacion.
                $location = $this->authorization->operationLocation($user);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($lockedOrder->almacen_id !== null && (int) $lockedOrder->almacen_id === $location['almacen_id'], 409, 'La reposición requiere operar desde el almacén original del pedido.');
                // Prepara en $salidas la consulta que obtiene o modifica los datos necesarios.
                $salidas = DB::table('movimientos_inventario')->where('documento_tipo', 'pedido')
                    ->where('documento_id', (string) $lockedOrder->id)->where('tipo_movimiento', 'salida')
                    ->where('empresa_id', $lockedOrder->empresa_id)->where('sede_id', $lockedOrder->sede_id)
                    ->where('almacen_id', $lockedOrder->almacen_id)->get();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_unless($salidas->count() === $lockedOrder->details->count(), 409, 'No se puede conciliar la salida de inventario de este pedido.');
                // Recorre los elementos recibidos para validar o procesar cada registro.
                foreach ($lockedOrder->details as $line) {
                    $claveSalida = $salidas->search(fn (object $movimiento): bool => (int) $movimiento->producto_id === (int) $line->producto_id
                        && (float) $movimiento->cantidad === (float) $line->cantidad
                        && $movimiento->variante_id == $line->variante_id);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($claveSalida !== false, 409, 'El detalle del pedido no coincide con su salida de inventario.');
                    $salidas->forget($claveSalida);
                    // Guarda en $product el resultado necesario para continuar la operacion.
                    $product = Producto::whereKey($line->producto_id)->lockForUpdate()->firstOrFail();
                    // Guarda en $entrada el resultado necesario para continuar la operacion.
                    $entrada = $this->inventoryService->cambiarProductoBloqueado($product, 'entrada', (int) $line->cantidad, 'Reingreso por cancelación del pedido #'.$lockedOrder->id, $user);
                    // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                    abort_unless($entrada->variante_id == $line->variante_id, 409, 'La variante del pedido no coincide con el saldo a reponer.');
                    $entrada->update(['documento_tipo' => 'pedido_cancelacion', 'documento_id' => (string) $lockedOrder->id]);
                }
            }

            $lockedOrder->update(['estado' => $status]);
            $this->auditLogger->record($user->id, 'order.status_changed', 'pedidos', $lockedOrder->id, ['estado' => $before], ['estado' => $status], $ip);
        }, 3);
    }

    public function registrarPagoPendiente(Pedido $order, ?int $paymentMethodId, ?int $userId): void
    {
        // Registra el pago pendiente asociado al pedido y medio seleccionado.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($paymentMethodId === null || ! Schema::hasTable('pagos')) {
            return;
        }

        // Prepara en $exists la consulta que obtiene o modifica los datos necesarios.
        $exists = DB::table('pagos')->where('pedido_id', $order->getKey())
            ->whereNotIn('estado', ['anulado', 'rechazado'])->exists();
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($exists) {
            return;
        }

        // Prepara en $methodExists la consulta que obtiene o modifica los datos necesarios.
        $methodExists = DB::table('medios_pago')->where('id', $paymentMethodId)->where('activo', true)->exists();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($methodExists, 422, 'El medio de pago no está disponible.');
        DB::table('pagos')->insert([
            'empresa_id' => $order->empresa_id, 'sede_id' => $order->sede_id,
            'codigo' => 'PED-'.$order->id.'-'.strtoupper(bin2hex(random_bytes(4))), 'tipo' => 'cobro', 'estado' => 'pendiente',
            'medio_pago_id' => $paymentMethodId, 'usuario_id' => $userId, 'pedido_id' => $order->id,
            'moneda' => 'PEN', 'importe' => $order->total, 'fecha_pago' => now(), 'creado_en' => now(),
        ]);
    }

    /** @param array{empresa_id:int,sede_id:int,almacen_id:int} $location */
    private function asegurarEnvio(Pedido $order, array $location, ?int $createdBy): void
    {
        // Crea o actualiza la informacion de envio del pedido.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasTable('envios_pedidos')) {
            return;
        }

        EnvioPedido::query()->firstOrCreate(
            ['pedido_id' => $order->getKey()],
            [
                ...$location, 'codigo' => 'ENV-'.str_pad((string) $order->getKey(), 8, '0', STR_PAD_LEFT),
                'modalidad' => 'domicilio', 'estado' => 'pendiente', 'moneda' => 'PEN',
                'destino_historico' => $location,
                'creado_por_usuario_id' => $createdBy, 'creado_en' => now(),
            ],
        );
    }

    /** @return array{descuento_centimos:int,cupon_id:?int} */
    private function descuentoPara(?string $couponCode, int $subtotalCents, int $empresaId): array
    {
        // Calcula el descuento aplicable sin superar el subtotal del pedido.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($couponCode === null || $couponCode === '') {
            return ['descuento_centimos' => 0, 'cupon_id' => null];
        }

        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasColumn('cupones', 'empresa_id'), 503,
            'Los cupones no están disponibles hasta configurar su empresa propietaria.');
        // Guarda en $coupon el resultado de la consulta ejecutada.
        $coupon = Cupon::where('empresa_id', $empresaId)->where('codigo', $couponCode)->lockForUpdate()->first();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($coupon && $coupon->activo && $coupon->inicia_en <= now() && $coupon->finaliza_en >= now(), 422, 'El cupón venció o fue desactivado.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($coupon->limite_usos !== null && $coupon->usos >= $coupon->limite_usos, 422, 'El cupón agotó sus usos.');
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($subtotalCents < (int) round((float) $coupon->minimo_compra * 100), 422, 'El pedido no alcanza el mínimo del cupón.');

        $discountCents = $coupon->tipo_descuento === 'porcentaje'
            ? (int) round($subtotalCents * ((float) $coupon->valor / 100), 0, PHP_ROUND_HALF_UP)
            : (int) round((float) $coupon->valor * 100, 0, PHP_ROUND_HALF_UP);

        $coupon->increment('usos');

        return ['descuento_centimos' => min($discountCents, $subtotalCents), 'cupon_id' => (int) $coupon->getKey()];
    }
}
