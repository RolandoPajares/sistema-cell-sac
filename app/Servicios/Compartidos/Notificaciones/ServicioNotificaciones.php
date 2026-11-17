<?php

namespace App\Servicios\Compartidos\Notificaciones;

use App\Models\Comunicacion\Notificacion;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioNotificaciones dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioNotificaciones
{
    /** @var array<int, string> */
    private const COLORES_GRAFICO = ['#0767f5', '#0bc4ee', '#9b82f6', '#77bbfb', '#8ce5db'];

    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $registradorAuditoria  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly RegistradorAuditoria $registradorAuditoria) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @return mixed Resultado de la operación.
     */
    public function listarDelUsuario(
        User $usuario,
        string $estado = 'todas',
        ?string $busqueda = null,
    ): LengthAwarePaginator {
        // ORM: consulta las notificaciones del usuario mediante Eloquent.
        // Guarda en $consulta el resultado necesario para continuar la operacion.
        $consulta = $this->consultaDelUsuario($usuario);

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($estado === 'no_leidas') {
            $consulta->whereNull('leido_en');
        } elseif ($estado === 'leidas') {
            $consulta->whereNotNull('leido_en');
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($busqueda !== null && trim($busqueda) !== '') {
            // Guarda en $termino el resultado necesario para continuar la operacion.
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
        // Guarda en $grupos el resultado necesario para continuar la operacion.
        $grupos = $this->consultaDelUsuario($usuario)
            ->select('tipo')
            ->selectRaw('COUNT(*) AS cantidad')
            ->selectRaw('SUM(CASE WHEN leido_en IS NULL THEN 1 ELSE 0 END) AS no_leidas')
            ->groupBy('tipo')
            ->orderByDesc('cantidad')
            ->get();
        $total = (int) $grupos->sum(static fn (Notificacion $notificacion): int => (int) $notificacion->getAttribute('cantidad'));
        $noLeidas = (int) $grupos->sum(static fn (Notificacion $notificacion): int => (int) $notificacion->getAttribute('no_leidas'));
        // Guarda en $inicio el resultado necesario para continuar la operacion.
        $inicio = 0.0;
        // Agrupa en $segmentos los datos que se utilizaran en el siguiente paso.
        $segmentos = [];
        // Agrupa en $distribucion los datos que se utilizaran en el siguiente paso.
        $distribucion = [];
        // Guarda en $cantidadTipos el resultado necesario para continuar la operacion.
        $cantidadTipos = $grupos->count();

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($grupos as $indice => $notificacion) {
            // Guarda en $cantidad el resultado necesario para continuar la operacion.
            $cantidad = (int) $notificacion->getAttribute('cantidad');
            $porcentaje = $total === 0 ? 0 : round(($cantidad / $total) * 100, 2);
            $fin = $indice === $cantidadTipos - 1 ? 100.0 : min(100.0, $inicio + $porcentaje);
            // Guarda en $color el resultado necesario para continuar la operacion.
            $color = self::COLORES_GRAFICO[$indice % count(self::COLORES_GRAFICO)];

            $segmentos[] = sprintf('%s %.2f%% %.2f%%', $color, $inicio, $fin);
            $distribucion[] = [
                'nombre' => class_basename((string) $notificacion->tipo),
                'valor' => $cantidad,
                'color' => $color,
            ];
            // Guarda en $inicio el resultado necesario para continuar la operacion.
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

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $identificador  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function buscarDelUsuario(User $usuario, string $identificador): ?Notificacion
    {
        return $this->consultaDelUsuario($usuario)->whereKey($identificador)->first();
    }

    /**
     * Ejecuta la operación marcarComoLeida definida por la clase ServicioNotificaciones.
     *
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $identificador  Parámetro recibido por la operación.
     * @param  mixed  $direccionIp  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    public function marcarComoLeida(User $usuario, string $identificador, ?string $direccionIp): bool
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasTable('registros_auditoria'), 503, 'La lectura requiere la tabla de auditoría disponible.');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($usuario, $identificador, $direccionIp): bool {
            // Guarda en $notificacion el resultado necesario para continuar la operacion.
            $notificacion = $this->consultaDelUsuario($usuario)
                ->whereKey($identificador)
                ->lockForUpdate()
                ->firstOrFail();

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($notificacion->leido_en !== null) {
                return false;
            }

            // Guarda en $fechaLectura el resultado necesario para continuar la operacion.
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
        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return Notificacion::query()
            ->where('tipo_notificable', $usuario->getMorphClass())
            ->where('notificable_id', $usuario->getKey());
    }
}
