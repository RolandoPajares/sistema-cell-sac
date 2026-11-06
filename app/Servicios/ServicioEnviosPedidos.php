<?php

namespace App\Servicios;

use App\Models\Comercio\EnvioPedido;
use App\Models\Comercio\EventoEnvioPedido;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Define la responsabilidad de la clase ServicioEnviosPedidos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioEnviosPedidos
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly RegistradorAuditoria $auditoria,
    ) {}

    /**
     * Actualiza el estado operativo y registra un evento sin duplicar notificaciones externas.
     *
     * @param  array{estado:string,descripcion?:string|null,identificador_externo?:string|null,ocurrido_en?:string|null}  $datos
     */
    public function actualizarEstado(EnvioPedido $envio, array $datos, User $usuario, ?string $ip = null): EventoEnvioPedido
    {
        abort_unless($this->autorizacion->allowsRecord(
            $usuario,
            'gestion.inventario.ajustar',
            (int) $envio->empresa_id,
            (int) $envio->sede_id,
        ), 403);

        return DB::transaction(function () use ($envio, $datos, $usuario, $ip): EventoEnvioPedido {
            $locked = EnvioPedido::query()->whereKey($envio->getKey())->lockForUpdate()->firstOrFail();
            $source = 'admin';
            $externalId = $datos['identificador_externo'] ?? null;
            if ($externalId !== null) {
                $existing = EventoEnvioPedido::query()
                    ->where('envio_pedido_id', $locked->getKey())
                    ->where('fuente', $source)
                    ->where('identificador_externo', $externalId)
                    ->first();
                if ($existing !== null) {
                    return $existing;
                }
            }

            $status = (string) $datos['estado'];
            abort_if(in_array($locked->estado, ['entregado', 'cancelado'], true) && $locked->estado !== $status, 409, 'El envío ya se encuentra en un estado terminal.');
            $occurredAt = isset($datos['ocurrido_en']) ? Carbon::parse($datos['ocurrido_en']) : now();
            $before = $locked->only(['estado', 'despachado_en', 'entregado_en']);
            $attributes = ['estado' => $status];
            if (in_array($status, ['despachado', 'en_transito'], true) && $locked->despachado_en === null) {
                $attributes['despachado_en'] = $occurredAt;
            }
            if ($status === 'entregado' && $locked->entregado_en === null) {
                $attributes['entregado_en'] = $occurredAt;
            }
            $locked->update($attributes);
            $event = EventoEnvioPedido::query()->create([
                'envio_pedido_id' => $locked->getKey(),
                'estado' => $status,
                'descripcion' => $datos['descripcion'] ?? null,
                'fuente' => $source,
                'identificador_externo' => $externalId,
                'ocurrido_en' => $occurredAt,
                'datos_seguros' => ['origen' => 'panel_administrativo'],
            ]);
            $this->auditoria->record($usuario->getKey(), 'shipment.status_updated', 'envios_pedidos', $locked->getKey(), $before, $locked->only(['estado', 'despachado_en', 'entregado_en']), $ip, 'exito', $datos['descripcion'] ?? null, ['empresa_id' => (int) $locked->empresa_id, 'sede_id' => (int) $locked->sede_id]);

            return $event;
        }, 3);
    }

    /**
     * @param  array{buscar?:string|null,estado?:string|null,modalidad?:string|null}  $filtros
     * @return array{
     *     envios:LengthAwarePaginator,
     *     resumen:array{total:int,con_seguimiento:int,con_guia:int,modalidades:int},
     *     distribucion:array<int, array{estado:string,cantidad:int}>,
     *     estados:array<int,string>,
     *     modalidades:array<int,string>
     * }
     */
    public function listar(User $usuario, array $filtros): array
    {
        $sedesAutorizadas = $this->autorizacion->authorizedSiteIds($usuario, 'gestion.inventario.consultar');
        $consultaBase = EnvioPedido::query();

        if ($sedesAutorizadas === []) {
            $consultaBase->whereRaw('1 = 0');
        } else {
            $consultaBase->whereIn('sede_id', $sedesAutorizadas);
        }

        $distribucion = (clone $consultaBase)
            ->select('estado')
            ->selectRaw('COUNT(*) AS cantidad')
            ->groupBy('estado')
            ->orderBy('estado')
            ->get()
            ->map(static fn (EnvioPedido $envio): array => [
                'estado' => (string) $envio->estado,
                'cantidad' => (int) $envio->getAttribute('cantidad'),
            ])
            ->all();

        $consultaFiltrada = clone $consultaBase;
        $this->aplicarFiltros($consultaFiltrada, $filtros);

        return [
            'envios' => $consultaFiltrada
                ->with(['almacen.sede', 'ultimoEvento'])
                ->orderByDesc('creado_en')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
            'resumen' => [
                'total' => (clone $consultaBase)->count(),
                'con_seguimiento' => (clone $consultaBase)
                    ->whereNotNull('numero_seguimiento')->where('numero_seguimiento', '<>', '')->count(),
                'con_guia' => (clone $consultaBase)->whereNotNull('guia_remision_id')->count(),
                'modalidades' => (clone $consultaBase)->distinct()->count('modalidad'),
            ],
            'distribucion' => $distribucion,
            'estados' => (clone $consultaBase)->distinct()->orderBy('estado')->pluck('estado')->all(),
            'modalidades' => (clone $consultaBase)->distinct()->orderBy('modalidad')->pluck('modalidad')->all(),
        ];
    }

    /** @return Builder<EnvioPedido> */
    public function consultaExportacion(User $usuario, array $filtros): Builder
    {
        $siteIds = $this->autorizacion->authorizedSiteIds($usuario, 'gestion.inventario.exportar');
        $query = EnvioPedido::query()->with(['almacen.sede', 'ultimoEvento']);
        if ($siteIds === []) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('sede_id', $siteIds);
        }
        $this->aplicarFiltros($query, $filtros);

        return $query->orderByDesc('creado_en')->orderByDesc('id');
    }

    /**
     * @param  Builder<EnvioPedido>  $consulta
     * @param  array{buscar?:string|null,estado?:string|null,modalidad?:string|null}  $filtros
     */
    private function aplicarFiltros(Builder $consulta, array $filtros): void
    {
        if (! empty($filtros['estado'])) {
            $consulta->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['modalidad'])) {
            $consulta->where('modalidad', $filtros['modalidad']);
        }

        $termino = trim((string) ($filtros['buscar'] ?? ''));
        if ($termino === '') {
            return;
        }

        $coincidencia = '%'.$termino.'%';
        $consulta->where(static function (Builder $consulta) use ($coincidencia, $termino): void {
            $consulta->where('codigo', 'like', $coincidencia)
                ->orWhere('transportista', 'like', $coincidencia)
                ->orWhere('servicio', 'like', $coincidencia)
                ->orWhere('numero_seguimiento', 'like', $coincidencia);

            if (ctype_digit($termino)) {
                $consulta->orWhere('pedido_id', (int) $termino);
            }
        });
    }
}
