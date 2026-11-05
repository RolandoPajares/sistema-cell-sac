<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\PrecioMayorista;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServicioPreciosMayoristas
{
    /** @param array{producto_id:int,precio:string,vigente_desde:string,vigente_hasta:?string,motivo:string} $datos */
    public function registrar(array $datos, User $usuario, ?string $ip): PrecioMayorista
    {
        abort_unless(Schema::hasTable('precios_mayoristas'), 503,
            'Los precios mayoristas requieren aplicar primero la estructura SQL aprobada.');
        $autorizacion = app(ServicioAutorizacion::class);
        abort_unless($autorizacion->allowsGlobal($usuario, 'gestion.productos.actualizar'), 403);
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para registrar precios mayoristas.');

        $inicio = CarbonImmutable::parse($datos['vigente_desde'])->startOfDay();
        $fin = isset($datos['vigente_hasta']) && $datos['vigente_hasta'] !== ''
            ? CarbonImmutable::parse($datos['vigente_hasta'])->endOfDay()
            : null;
        abort_if($fin !== null && $fin->lt($inicio), 422, 'La vigencia final no puede ser anterior al inicio.');

        return DB::transaction(function () use ($datos, $usuario, $ip, $empresaId, $inicio, $fin): PrecioMayorista {
            $producto = Producto::query()->whereKey($datos['producto_id'])->where('empresa_id', $empresaId)->lockForUpdate()->firstOrFail();
            $preciosEnConflicto = PrecioMayorista::query()->where('empresa_id', $empresaId)
                ->where('producto_id', $producto->getKey())->whereNull('variante_id')
                ->where('vigente_desde', '<=', $fin ?? '9999-12-31 23:59:59.999999')
                ->where(fn ($consulta) => $consulta->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', $inicio))
                ->orderBy('vigente_desde')->lockForUpdate()->get();

            foreach ($preciosEnConflicto as $precioExistente) {
                $inicioExistente = CarbonImmutable::parse($precioExistente->vigente_desde);
                abort_if($inicioExistente->greaterThanOrEqualTo($inicio), 409,
                    'La vigencia se cruza con un precio mayorista futuro. Ajusta las fechas antes de guardar.');
                $finAnterior = $inicio->subMicrosecond();
                abort_if($finAnterior->lt($inicioExistente), 409,
                    'La nueva vigencia dejaría sin intervalo al precio mayorista anterior.');

                $antes = ['vigente_hasta' => $precioExistente->vigente_hasta?->toDateTimeString()];
                $precioExistente->update(['vigente_hasta' => $finAnterior]);
                app(RegistradorAuditoria::class)->record(
                    (int) $usuario->getKey(), 'precio_mayorista.vigencia_cerrada', 'precios_mayoristas', $precioExistente->getKey(),
                    $antes, ['vigente_hasta' => $finAnterior->toDateTimeString()], $ip,
                    scope: ['empresa_id' => $empresaId],
                );
            }

            $precio = PrecioMayorista::query()->create([
                'empresa_id' => $empresaId,
                'producto_id' => $producto->getKey(),
                'variante_id' => null,
                'precio' => $datos['precio'],
                'vigente_desde' => $inicio,
                'vigente_hasta' => $fin,
                'motivo' => trim($datos['motivo']),
                'creado_por_usuario_id' => $usuario->getKey(),
            ]);
            app(RegistradorAuditoria::class)->record(
                (int) $usuario->getKey(), 'precio_mayorista.registrado', 'precios_mayoristas', $precio->getKey(), null,
                $precio->only(['empresa_id', 'producto_id', 'variante_id', 'precio', 'vigente_desde', 'vigente_hasta', 'motivo']), $ip,
                scope: ['empresa_id' => $empresaId],
            );

            return $precio;
        }, 3);
    }

    public function precioVigente(int $empresaId, int $productoId): ?PrecioMayorista
    {
        if (! Schema::hasTable('precios_mayoristas')) {
            return null;
        }

        return PrecioMayorista::query()->where('empresa_id', $empresaId)->where('producto_id', $productoId)
            ->whereNull('variante_id')->where('vigente_desde', '<=', now())
            ->where(fn ($consulta) => $consulta->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', now()))
            ->orderByDesc('vigente_desde')->orderByDesc('id')->first();
    }

    /** @param array<int, int> $productosIds
     * @return Collection<int, PrecioMayorista>
     */
    public function preciosVigentes(int $empresaId, array $productosIds): Collection
    {
        if (! Schema::hasTable('precios_mayoristas') || $productosIds === []) {
            return collect();
        }

        return PrecioMayorista::query()->where('empresa_id', $empresaId)->whereIn('producto_id', $productosIds)
            ->whereNull('variante_id')->where('vigente_desde', '<=', now())
            ->where(fn ($consulta) => $consulta->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', now()))
            ->orderByDesc('vigente_desde')->orderByDesc('id')->get()->unique('producto_id')->keyBy('producto_id');
    }
}
