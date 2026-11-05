<?php

namespace App\Servicios;

use App\Models\Comercio\EnvioPedido;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ServicioEnviosPedidos
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

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
