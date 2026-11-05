<?php

namespace App\Servicios;

use App\Models\Comunicacion\Notificacion;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ServicioNotificaciones
{
    /** @var array<int, string> */
    private const COLORES_GRAFICO = ['#0767f5', '#0bc4ee', '#9b82f6', '#77bbfb', '#8ce5db'];

    public function __construct(private readonly RegistradorAuditoria $registradorAuditoria) {}

    public function listarDelUsuario(
        User $usuario,
        string $estado = 'todas',
        ?string $busqueda = null,
    ): LengthAwarePaginator {
        $consulta = $this->consultaDelUsuario($usuario);

        if ($estado === 'no_leidas') {
            $consulta->whereNull('leido_en');
        } elseif ($estado === 'leidas') {
            $consulta->whereNotNull('leido_en');
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $termino = '%'.trim($busqueda).'%';
            $consulta->where(function (Builder $consulta) use ($termino): void {
                $consulta->where('tipo', 'like', $termino)
                    ->orWhere('datos', 'like', $termino);
            });
        }

        return $consulta->orderByDesc('creado_en')->orderByDesc('id')->paginate(20)->withQueryString();
    }

    /**
     * @return array{
     *     total:int,
     *     no_leidas:int,
     *     leidas:int,
     *     tipos:int,
     *     distribucion:array<int, array{nombre:string,valor:int,color:string}>,
     *     gradiente:string
     * }
     */
    public function obtenerResumenDelUsuario(User $usuario): array
    {
        $grupos = $this->consultaDelUsuario($usuario)
            ->select('tipo')
            ->selectRaw('COUNT(*) AS cantidad')
            ->selectRaw('SUM(CASE WHEN leido_en IS NULL THEN 1 ELSE 0 END) AS no_leidas')
            ->groupBy('tipo')
            ->orderByDesc('cantidad')
            ->get();
        $total = (int) $grupos->sum(static fn (Notificacion $notificacion): int => (int) $notificacion->getAttribute('cantidad'));
        $noLeidas = (int) $grupos->sum(static fn (Notificacion $notificacion): int => (int) $notificacion->getAttribute('no_leidas'));
        $inicio = 0.0;
        $segmentos = [];
        $distribucion = [];
        $cantidadTipos = $grupos->count();

        foreach ($grupos as $indice => $notificacion) {
            $cantidad = (int) $notificacion->getAttribute('cantidad');
            $porcentaje = $total === 0 ? 0 : round(($cantidad / $total) * 100, 2);
            $fin = $indice === $cantidadTipos - 1 ? 100.0 : min(100.0, $inicio + $porcentaje);
            $color = self::COLORES_GRAFICO[$indice % count(self::COLORES_GRAFICO)];

            $segmentos[] = sprintf('%s %.2f%% %.2f%%', $color, $inicio, $fin);
            $distribucion[] = [
                'nombre' => class_basename((string) $notificacion->tipo),
                'valor' => $cantidad,
                'color' => $color,
            ];
            $inicio = $fin;
        }

        return [
            'total' => $total,
            'no_leidas' => $noLeidas,
            'leidas' => $total - $noLeidas,
            'tipos' => $cantidadTipos,
            'distribucion' => $distribucion,
            'gradiente' => $segmentos === []
                ? 'conic-gradient(#dce9f8 0 100%)'
                : 'conic-gradient('.implode(', ', $segmentos).')',
        ];
    }

    public function buscarDelUsuario(User $usuario, string $identificador): ?Notificacion
    {
        return $this->consultaDelUsuario($usuario)->whereKey($identificador)->first();
    }

    public function marcarComoLeida(User $usuario, string $identificador, ?string $direccionIp): bool
    {
        abort_unless(Schema::hasTable('registros_auditoria'), 503, 'La lectura requiere la tabla de auditoría disponible.');

        return DB::transaction(function () use ($usuario, $identificador, $direccionIp): bool {
            $notificacion = $this->consultaDelUsuario($usuario)
                ->whereKey($identificador)
                ->lockForUpdate()
                ->firstOrFail();

            if ($notificacion->leido_en !== null) {
                return false;
            }

            $fechaLectura = now();
            $notificacion->forceFill(['leido_en' => $fechaLectura])->save();
            $this->registradorAuditoria->record(
                (int) $usuario->getKey(),
                'marcar_notificacion_leida',
                'notificaciones',
                null,
                ['identificador' => $notificacion->getKey(), 'leido_en' => null],
                ['identificador' => $notificacion->getKey(), 'leido_en' => $fechaLectura->toIso8601String()],
                $direccionIp,
            );

            return true;
        });
    }

    /** @return Builder<Notificacion> */
    private function consultaDelUsuario(User $usuario): Builder
    {
        return Notificacion::query()
            ->where('tipo_notificable', $usuario->getMorphClass())
            ->where('notificable_id', $usuario->getKey());
    }
}
