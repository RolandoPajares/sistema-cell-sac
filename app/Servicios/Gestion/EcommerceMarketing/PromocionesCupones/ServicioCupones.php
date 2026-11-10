<?php

namespace App\Servicios\Gestion\EcommerceMarketing\PromocionesCupones;

use App\Models\Comercio\Cupon;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioCupones dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioCupones
{
    /**
     * @param  array{buscar?:?string,tipo?:?string,estado?:?string,vigencia?:?string}  $filters
     * @return Builder<Cupon>
     */
    public function query(array $filters, ?int $empresaId): Builder
    {
        $query = Cupon::query();
        if (! Schema::hasColumn('cupones', 'empresa_id') || $empresaId === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('empresa_id', $empresaId)
            ->when($filters['buscar'] ?? null, fn (Builder $query, string $search): Builder => $query->where('codigo', 'like', '%'.$search.'%'))
            ->when($filters['tipo'] ?? null, fn (Builder $query, string $type): Builder => $query->where('tipo_descuento', $type));

        $now = now();
        match ($filters['estado'] ?? null) {
            'Activo' => $query->where('activo', true)
                ->where('inicia_en', '<=', $now)
                ->where('finaliza_en', '>=', $now)
                ->where(fn (Builder $query): Builder => $query->whereNull('limite_usos')->orWhereColumn('usos', '<', 'limite_usos')),
            'Programado' => $query->where('activo', true)->where('inicia_en', '>', $now),
            'Finalizado' => $query->where(fn (Builder $query): Builder => $query->where('activo', false)
                ->orWhere('finaliza_en', '<', $now)
                ->orWhere(fn (Builder $query): Builder => $query->whereNotNull('limite_usos')->whereColumn('usos', '>=', 'limite_usos'))),
            default => null,
        };

        match ($filters['vigencia'] ?? null) {
            'este_mes' => $query->whereBetween('finaliza_en', [now()->startOfMonth(), now()->endOfMonth()]),
            'proximos_30_dias' => $query->whereBetween('finaliza_en', [now(), now()->addDays(30)]),
            'historico' => $query->where('finaliza_en', '<', now()),
            default => null,
        };

        return $query;
    }

    /** @return array{total:int,activos:int,canjes:int,proximos:iterable,mas_usados:iterable} */
    public function dashboard(?int $empresaId): array
    {
        if (! Schema::hasColumn('cupones', 'empresa_id') || $empresaId === null) {
            return ['total' => 0, 'activos' => 0, 'canjes' => 0, 'proximos' => collect(), 'mas_usados' => collect()];
        }

        $now = now();
        $cuponesEmpresa = Cupon::query()->where('empresa_id', $empresaId);

        return [
            'total' => (clone $cuponesEmpresa)->count(),
            'activos' => (clone $cuponesEmpresa)->where('activo', true)->where('inicia_en', '<=', $now)->where('finaliza_en', '>=', $now)
                ->where(fn (Builder $query): Builder => $query->whereNull('limite_usos')->orWhereColumn('usos', '<', 'limite_usos'))->count(),
            'canjes' => (int) (clone $cuponesEmpresa)->sum('usos'),
            'proximos' => (clone $cuponesEmpresa)->where('activo', true)->whereBetween('finaliza_en', [$now, $now->copy()->addDays(30)])
                ->orderBy('finaliza_en')->orderBy('id')->limit(4)->get(),
            'mas_usados' => (clone $cuponesEmpresa)->where('usos', '>', 0)->orderByDesc('usos')->orderBy('id')->limit(5)->get(),
        ];
    }

    /** @param array<string, mixed> $attributes */
    public function register(array $attributes, User $actor, ?string $ip): Cupon
    {
        $this->authorization()->assertAllowed($actor, 'gestion.cupones.crear');
        $empresaId = $this->empresaActual();
        $attributes['empresa_id'] = $empresaId;
        $attributes['codigo'] = mb_strtoupper((string) $attributes['codigo']);
        $this->assertCouponRules($attributes, 0);

        return DB::transaction(function () use ($attributes, $actor, $ip, $empresaId): Cupon {
            $coupon = Cupon::query()->create($attributes + ['activo' => true, 'usos' => 0]);
            app(RegistradorAuditoria::class)->record(
                $actor->getKey(), 'coupon.created', 'cupones', $coupon->getKey(), null,
                $coupon->only(['empresa_id', 'codigo', 'tipo_descuento', 'valor', 'minimo_compra', 'limite_usos', 'inicia_en', 'finaliza_en']), $ip,
                scope: ['empresa_id' => $empresaId],
            );

            return $coupon;
        }, 3);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Cupon $coupon, array $attributes, User $actor, ?string $ip): Cupon
    {
        $this->authorization()->assertAllowed($actor, 'gestion.cupones.actualizar');
        $empresaId = $this->empresaActual();

        return DB::transaction(function () use ($coupon, $attributes, $actor, $ip, $empresaId): Cupon {
            $lockedCoupon = Cupon::query()->where('empresa_id', $empresaId)->whereKey($coupon->getKey())->lockForUpdate()->firstOrFail();
            $this->assertCouponRules($attributes, (int) $lockedCoupon->usos);
            $before = $lockedCoupon->only(['tipo_descuento', 'valor', 'minimo_compra', 'limite_usos', 'inicia_en', 'finaliza_en']);
            $lockedCoupon->update($attributes + ['actualizado_en' => now()]);
            app(RegistradorAuditoria::class)->record(
                $actor->getKey(), 'coupon.updated', 'cupones', $lockedCoupon->getKey(), $before,
                $lockedCoupon->only(array_keys($before)), $ip, scope: ['empresa_id' => $empresaId],
            );

            return $lockedCoupon->refresh();
        }, 3);
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $coupon  Parámetro recibido por la operación.
     * @param  mixed  $actor  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return Cupon Resultado de la operación.
     */
    public function deactivate(Cupon $coupon, User $actor, ?string $ip): Cupon
    {
        $this->authorization()->assertAllowed($actor, 'gestion.cupones.eliminar');
        $empresaId = $this->empresaActual();

        return DB::transaction(function () use ($coupon, $actor, $ip, $empresaId): Cupon {
            $lockedCoupon = Cupon::query()->where('empresa_id', $empresaId)->whereKey($coupon->getKey())->lockForUpdate()->firstOrFail();
            if (! $lockedCoupon->activo) {
                return $lockedCoupon;
            }

            $lockedCoupon->update(['activo' => false, 'actualizado_en' => now()]);
            app(RegistradorAuditoria::class)->record(
                $actor->getKey(), 'coupon.deactivated', 'cupones', $lockedCoupon->getKey(), ['activo' => true], ['activo' => false], $ip,
                scope: ['empresa_id' => $empresaId],
            );

            return $lockedCoupon->refresh();
        }, 3);
    }

    /**
     * Ejecuta la operación status definida por la clase ServicioCupones.
     *
     * @param  mixed  $coupon  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    public function status(Cupon $coupon): string
    {
        if (! $coupon->activo || $coupon->finaliza_en->isPast()
            || ($coupon->limite_usos !== null && (int) $coupon->usos >= (int) $coupon->limite_usos)) {
            return 'Finalizado';
        }

        return $coupon->inicia_en->isFuture() ? 'Programado' : 'Activo';
    }

    /** @param array<string, mixed> $attributes */
    private function assertCouponRules(array $attributes, int $registeredUses): void
    {
        abort_if(($attributes['tipo_descuento'] ?? null) === 'porcentaje' && (float) ($attributes['valor'] ?? 0) > 100, 422, 'El descuento porcentual no puede superar el 100 %.');
        if (isset($attributes['limite_usos'])) {
            abort_if((int) $attributes['limite_usos'] < $registeredUses, 422, 'El límite de usos no puede ser inferior a los canjes ya registrados.');
        }
    }

    /**
     * Ejecuta la operación authorization definida por la clase ServicioCupones.
     *
     * @return ServicioAutorizacion Resultado de la operación.
     */
    private function authorization(): ServicioAutorizacion
    {
        return app(ServicioAutorizacion::class);
    }

    /**
     * Ejecuta la operación empresaDisponible definida por la clase ServicioCupones.
     *
     * @return bool Resultado de la operación.
     */
    public function empresaDisponible(): bool
    {
        return Schema::hasColumn('cupones', 'empresa_id') && $this->authorization()->activeCompanyId() !== null;
    }

    /**
     * Ejecuta la operación empresaActual definida por la clase ServicioCupones.
     *
     * @return int Resultado de la operación.
     */
    private function empresaActual(): int
    {
        abort_unless(Schema::hasColumn('cupones', 'empresa_id'), 503,
            'Los cupones están deshabilitados hasta aplicar la estructura de propiedad por empresa.');
        $empresaId = $this->authorization()->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para gestionar cupones.');

        return $empresaId;
    }
}
