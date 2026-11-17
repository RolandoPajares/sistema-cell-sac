<?php

namespace App\Servicios\Gestion\Finanzas;

use App\Models\Comercio\DevolucionGarantia;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\Compras\Compra;
use App\Models\Finanzas\CuentaBancaria;
use App\Models\Finanzas\MedioPago;
use App\Models\Finanzas\Pago;
use App\Models\Finanzas\Reembolso;
use App\Models\Finanzas\SesionCaja;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Define la responsabilidad de la clase ServicioConsultasFinancieras dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioConsultasFinancieras
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $authorization  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private ServicioAutorizacion $authorization) {}

    /**
     * @param  array{buscar?:string|null,tipo?:string|null,estado?:string|null,desde?:string|null,hasta?:string|null}  $filters
     * @return Builder<Pago>
     */
    public function pagos(User $actor, array $filters): Builder
    {
        $this->authorization->assertAllowed($actor, 'gestion.pagos.consultar');

        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        return $this->authorization->scopeRecordsFor(Pago::query(), $actor, 'gestion.pagos.consultar')
            ->with([
                'medioPago:id,nombre',
                'usuario:id,nombre',
                'pedido:id,usuario_id',
                'pedido.user:id,nombre',
                'venta:id,cliente_id,codigo',
                'venta.cliente:id,nombre_contacto,razon_social',
                'compra:id,proveedor_id,numero_referencia',
                'compra.supplier:id,nombre',
            ])
            ->when($filters['buscar'] ?? null, static function (Builder $query, string $search): void {
                $query->where(static function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('codigo', 'like', '%'.$search.'%')
                        ->orWhere('referencia', 'like', '%'.$search.'%');
                    // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                    if (ctype_digit($search)) {
                        $searchQuery->orWhere('pedido_id', (int) $search)
                            ->orWhere('venta_id', (int) $search)
                            ->orWhere('compra_id', (int) $search);
                    }
                });
            })
            ->when($filters['tipo'] ?? null, static fn (Builder $query, string $type): Builder => $query->where('tipo', $type))
            ->when($filters['estado'] ?? null, static fn (Builder $query, string $state): Builder => $query->where('estado', $state))
            ->when($filters['desde'] ?? null, static fn (Builder $query, string $date): Builder => $query->whereDate('fecha_pago', '>=', $date))
            ->when($filters['hasta'] ?? null, static fn (Builder $query, string $date): Builder => $query->whereDate('fecha_pago', '<=', $date));
    }

    /**
     * @param  array{buscar?:string|null,estado?:string|null,desde?:string|null,hasta?:string|null}  $filters
     * @return Builder<Reembolso>
     */
    public function reembolsos(User $actor, array $filters): Builder
    {
        $this->authorization->assertAllowed($actor, 'gestion.reembolsos.consultar');

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return Reembolso::query()
            ->with([
                'pago:id,codigo,empresa_id,sede_id',
                'devolucion:id,codigo,cliente_id',
                'devolucion.customer:id,nombre_contacto,razon_social',
            ])
            ->whereHas('pago', function (Builder $paymentQuery) use ($actor): void {
                $this->authorization->scopeRecordsFor($paymentQuery, $actor, 'gestion.reembolsos.consultar');
            })
            ->when($filters['buscar'] ?? null, static function (Builder $query, string $search): void {
                $query->where(static function (Builder $searchQuery) use ($search): void {
                    $searchQuery->whereHas('pago', static fn (Builder $paymentQuery): Builder => $paymentQuery->where('codigo', 'like', '%'.$search.'%'))
                        ->orWhereHas('devolucion', static fn (Builder $returnQuery): Builder => $returnQuery->where('codigo', 'like', '%'.$search.'%'));
                    // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                    if (ctype_digit($search)) {
                        $searchQuery->orWhere('id', (int) $search);
                    }
                });
            })
            ->when($filters['estado'] ?? null, static fn (Builder $query, string $state): Builder => $query->where('estado', $state))
            ->when($filters['desde'] ?? null, static fn (Builder $query, string $date): Builder => $query->whereDate('creado_en', '>=', $date))
            ->when($filters['hasta'] ?? null, static fn (Builder $query, string $date): Builder => $query->whereDate('creado_en', '<=', $date));
    }

    /** @return array{medios:Collection,documentos:Collection,sesiones:Collection,cuentas:Collection} */
    public function opcionesPago(User $actor): array
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.pagos.crear';
        // Guarda en $location el resultado necesario para continuar la operacion.
        $location = $this->sedeParaFormulario($actor);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($location['empresa_id'] === null || $location['sede_id'] === null
            || ! $this->authorization->allowsRecord($actor, $permission, $location['empresa_id'], $location['sede_id'])) {
            return ['medios' => collect(), 'documentos' => collect(), 'sesiones' => collect(), 'cuentas' => collect()];
        }
        // Guarda en $companyId el resultado necesario para continuar la operacion.
        $companyId = $location['empresa_id'];
        // Guarda en $siteId el resultado necesario para continuar la operacion.
        $siteId = $location['sede_id'];
        // Prepara en $methods la consulta que obtiene o modifica los datos necesarios.
        $methods = MedioPago::query()->where('activo', true)->orderBy('nombre')
            ->get(['id', 'nombre', 'requiere_caja', 'requiere_banco']);
        // Prepara en $sessions la consulta que obtiene o modifica los datos necesarios.
        $sessions = SesionCaja::query()->where('estado', 'abierta')
            ->whereHas('caja', static fn (Builder $box): Builder => $box->where('sede_id', $siteId)->where('activa', true))
            ->with('caja:id,codigo')->orderByDesc('id')->limit(50)->get(['id', 'caja_id']);
        // Prepara en $accounts la consulta que obtiene o modifica los datos necesarios.
        $accounts = CuentaBancaria::query()->where('empresa_id', $companyId)->where('activa', true)
            ->orderBy('codigo')->get(['id', 'codigo', 'banco', 'moneda']);
        // Prepara en $orders la consulta que obtiene o modifica los datos necesarios.
        $orders = Pedido::query()->where('empresa_id', $companyId)->where('sede_id', $siteId)
            ->whereNotIn('estado', ['Anulado', 'Anulada', 'Cancelado', 'Cancelada', 'Rechazada'])
            ->whereNotIn('id', Venta::query()->whereNotNull('pedido_id')->select('pedido_id'))
            ->orderByDesc('id')->limit(50)->get(['id', 'total'])
            ->map(static fn (Pedido $order): array => [
                'value' => 'pedido:'.$order->id,
                'label' => 'Pedido #'.$order->id.' · PEN '.$order->total,
            ]);
        // Prepara en $sales la consulta que obtiene o modifica los datos necesarios.
        $sales = Venta::query()->where('empresa_id', $companyId)->where('sede_id', $siteId)
            ->whereNotIn('estado', ['anulado', 'anulada', 'cancelado', 'cancelada'])
            ->orderByDesc('id')->limit(50)->get(['id', 'codigo', 'total', 'moneda'])
            ->map(static fn (Venta $sale): array => [
                'value' => 'venta:'.$sale->id,
                'label' => 'Venta '.$sale->codigo.' · '.$sale->moneda.' '.$sale->total,
            ]);
        // Prepara en $purchases la consulta que obtiene o modifica los datos necesarios.
        $purchases = Compra::query()->where('empresa_id', $companyId)->where('sede_id', $siteId)
            ->where('activo', true)->whereNotIn('estado', ['Anulada', 'Cancelada', 'Rechazada'])
            ->orderByDesc('id')->limit(50)->get(['id', 'numero_referencia', 'total', 'moneda'])
            ->map(static fn (Compra $purchase): array => [
                'value' => 'compra:'.$purchase->id,
                'label' => 'Compra '.($purchase->numero_referencia ?: '#'.$purchase->id).' · '.$purchase->moneda.' '.$purchase->total,
            ]);

        return ['medios' => $methods, 'documentos' => $orders->concat($sales)->concat($purchases),
            'sesiones' => $sessions, 'cuentas' => $accounts];
    }

    /** @return array{devoluciones:Collection,cobros:Collection} */
    public function opcionesReembolso(User $actor): array
    {
        // Guarda en $permission el resultado necesario para continuar la operacion.
        $permission = 'gestion.reembolsos.crear';
        // Guarda en $location el resultado necesario para continuar la operacion.
        $location = $this->sedeParaFormulario($actor);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($location['empresa_id'] === null || $location['sede_id'] === null
            || ! $this->authorization->allowsRecord($actor, $permission, $location['empresa_id'], $location['sede_id'])) {
            return ['devoluciones' => collect(), 'cobros' => collect()];
        }
        // Guarda en $companyId el resultado necesario para continuar la operacion.
        $companyId = $location['empresa_id'];
        // Guarda en $siteId el resultado necesario para continuar la operacion.
        $siteId = $location['sede_id'];

        return [
            'devoluciones' => DevolucionGarantia::query()->where('empresa_id', $companyId)->where('sede_id', $siteId)
                ->where('activo', true)->where('tipo', 'Devolución')->where('estado', 'Resuelta')
                ->whereNotNull('resolucion')->orderByDesc('id')->limit(100)->get(['id', 'codigo']),
            'cobros' => Pago::query()->where('empresa_id', $companyId)->where('sede_id', $siteId)
                ->where('tipo', 'cobro')->whereNotIn('estado', ['pendiente', 'anulado'])
                ->whereDoesntHave('reembolso')->orderByDesc('id')->limit(100)->get(['id', 'codigo', 'importe', 'moneda']),
        ];
    }

    /** @return array{empresa_id:?int,sede_id:?int} */
    private function sedeParaFormulario(User $actor): array
    {
        try {
            return $this->authorization->operationSite($actor);
        } catch (HttpException $exception) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($exception->getStatusCode() !== 403) {
                throw $exception;
            }

            return ['empresa_id' => null, 'sede_id' => null];
        }
    }
}
