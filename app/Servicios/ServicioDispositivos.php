<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\Dispositivo;
use App\Models\Organizacion\Almacen;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ServicioDispositivos
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * @param  array{buscar?:string|null,producto?:int|string|null,almacen?:int|string|null,estado?:string|null}  $filtros
     * @return array{
     *     dispositivos:LengthAwarePaginator,
     *     resumen:array{total:int,con_imei:int,con_serie:int,estados:int},
     *     distribucion:array<int, array{estado:string,cantidad:int}>,
     *     productos:iterable<Producto>,
     *     almacenes:iterable<Almacen>,
     *     estados:array<int,string>
     * }
     */
    public function listar(User $usuario, array $filtros): array
    {
        $sedesAutorizadas = $this->autorizacion->authorizedSiteIds($usuario, 'gestion.inventario.consultar');
        $consultaBase = $this->consultaPorSedes($sedesAutorizadas);
        $distribucion = (clone $consultaBase)
            ->select('estado')
            ->selectRaw('COUNT(*) AS cantidad')
            ->groupBy('estado')
            ->orderBy('estado')
            ->get()
            ->map(static fn (Dispositivo $dispositivo): array => [
                'estado' => (string) $dispositivo->estado,
                'cantidad' => (int) $dispositivo->getAttribute('cantidad'),
            ])
            ->all();

        $consultaFiltrada = clone $consultaBase;
        $this->aplicarFiltros($consultaFiltrada, $filtros);

        $dispositivos = $consultaFiltrada
            ->with(['producto', 'variante', 'almacen.sede', 'ultimoMovimiento'])
            ->orderByDesc('fecha_ingreso')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return [
            'dispositivos' => $dispositivos,
            'resumen' => [
                'total' => (clone $consultaBase)->count(),
                'con_imei' => (clone $consultaBase)->whereNotNull('imei')->count(),
                'con_serie' => (clone $consultaBase)->whereNotNull('numero_serie')->count(),
                'estados' => count($distribucion),
            ],
            'distribucion' => $distribucion,
            'productos' => Producto::query()
                ->whereIn('id', (clone $consultaBase)->select('producto_id'))
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'marca']),
            'almacenes' => Almacen::query()
                ->whereIn('sede_id', $sedesAutorizadas)
                ->whereIn('id', (clone $consultaBase)->select('almacen_actual_id'))
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'sede_id']),
            'estados' => (clone $consultaBase)->distinct()->orderBy('estado')->pluck('estado')->all(),
        ];
    }

    /**
     * @param  array<int,int>  $sedesAutorizadas
     * @return Builder<Dispositivo>
     */
    private function consultaPorSedes(array $sedesAutorizadas): Builder
    {
        return Dispositivo::query()
            ->when(
                $sedesAutorizadas === [],
                static fn (Builder $consulta): Builder => $consulta->whereRaw('1 = 0'),
                static fn (Builder $consulta): Builder => $consulta->whereHas(
                    'almacen',
                    static fn (Builder $consultaAlmacen): Builder => $consultaAlmacen->whereIn('sede_id', $sedesAutorizadas),
                ),
            );
    }

    /**
     * @param  Builder<Dispositivo>  $consulta
     * @param  array{buscar?:string|null,producto?:int|string|null,almacen?:int|string|null,estado?:string|null}  $filtros
     */
    private function aplicarFiltros(Builder $consulta, array $filtros): void
    {
        if (! empty($filtros['producto'])) {
            $consulta->where('producto_id', (int) $filtros['producto']);
        }

        if (! empty($filtros['almacen'])) {
            $consulta->where('almacen_actual_id', (int) $filtros['almacen']);
        }

        if (! empty($filtros['estado'])) {
            $consulta->where('estado', $filtros['estado']);
        }

        $termino = trim((string) ($filtros['buscar'] ?? ''));
        if ($termino === '') {
            return;
        }

        $coincidencia = '%'.$termino.'%';
        $consulta->where(static function (Builder $consulta) use ($coincidencia): void {
            $consulta->where('imei', 'like', $coincidencia)
                ->orWhere('numero_serie', 'like', $coincidencia)
                ->orWhereHas('producto', static function (Builder $consultaProducto) use ($coincidencia): void {
                    $consultaProducto->where('nombre', 'like', $coincidencia)
                        ->orWhere('marca', 'like', $coincidencia);
                })
                ->orWhereHas('variante', static function (Builder $consultaVariante) use ($coincidencia): void {
                    $consultaVariante->where('codigo_sku', 'like', $coincidencia)
                        ->orWhere('codigo_barras', 'like', $coincidencia)
                        ->orWhere('nombre_color', 'like', $coincidencia);
                });
        });
    }
}
