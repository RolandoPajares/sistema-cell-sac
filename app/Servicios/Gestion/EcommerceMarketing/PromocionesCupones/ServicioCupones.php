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
        // Prepara en $query la consulta que obtiene o modifica los datos necesarios.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        $query = Cupon::query();
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasColumn('cupones', 'empresa_id') || $empresaId === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('empresa_id', $empresaId)
            ->when($filters['buscar'] ?? null, fn (Builder $query, string $search): Builder => $query->where('codigo', 'like', '%'.$search.'%'))
            ->when($filters['tipo'] ?? null, fn (Builder $query, string $type): Builder => $query->where('tipo_descuento', $type));

        // Guarda en $now el resultado necesario para continuar la operacion.
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
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasColumn('cupones', 'empresa_id') || $empresaId === null) {
            return ['total' => 0, 'activos' => 0, 'canjes' => 0, 'proximos' => collect(), 'mas_usados' => collect()];
        }

        // Guarda en $now el resultado necesario para continuar la operacion.
        $now = now();
        // Prepara en $cuponesEmpresa la consulta que obtiene o modifica los datos necesarios.
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
    public function registrar(array $attributes, User $actor, ?string $ip): Cupon
    {
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->autorizacion()->assertAllowed($actor, 'gestion.cupones.crear');
        // Guarda en $empresaId el resultado necesario para continuar la operacion.
        $empresaId = $this->empresaActual();
        $attributes['empresa_id'] = $empresaId;
        $attributes['codigo'] = mb_strtoupper((string) $attributes['codigo']);
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->comprobarReglasCupon($attributes, 0);

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
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
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->autorizacion()->assertAllowed($actor, 'gestion.cupones.actualizar');
        // Guarda en $empresaId el resultado necesario para continuar la operacion.
        $empresaId = $this->empresaActual();

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($coupon, $attributes, $actor, $ip, $empresaId): Cupon {
            // Prepara en $lockedCoupon la consulta que obtiene o modifica los datos necesarios.
            $lockedCoupon = Cupon::query()->where('empresa_id', $empresaId)->whereKey($coupon->getKey())->lockForUpdate()->firstOrFail();
            // Delega esta parte de la operacion al servicio o dependencia correspondiente.
            $this->comprobarReglasCupon($attributes, (int) $lockedCoupon->usos);
            // Guarda en $before el resultado necesario para continuar la operacion.
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
        // Delega esta parte de la operacion al servicio o dependencia correspondiente.
        $this->autorizacion()->assertAllowed($actor, 'gestion.cupones.eliminar');
        // Guarda en $empresaId el resultado necesario para continuar la operacion.
        $empresaId = $this->empresaActual();

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($coupon, $actor, $ip, $empresaId): Cupon {
            // Prepara en $lockedCoupon la consulta que obtiene o modifica los datos necesarios.
            $lockedCoupon = Cupon::query()->where('empresa_id', $empresaId)->whereKey($coupon->getKey())->lockForUpdate()->firstOrFail();
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $coupon->activo || $coupon->finaliza_en->isPast()
            || ($coupon->limite_usos !== null && (int) $coupon->usos >= (int) $coupon->limite_usos)) {
            return 'Finalizado';
        }

        return $coupon->inicia_en->isFuture() ? 'Programado' : 'Activo';
    }

    /** @param array<string, mixed> $attributes */
    private function comprobarReglasCupon(array $attributes, int $registeredUses): void
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if(($attributes['tipo_descuento'] ?? null) === 'porcentaje' && (float) ($attributes['valor'] ?? 0) > 100, 422, 'El descuento porcentual no puede superar el 100 %.');
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (isset($attributes['limite_usos'])) {
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_if((int) $attributes['limite_usos'] < $registeredUses, 422, 'El límite de usos no puede ser inferior a los canjes ya registrados.');
        }
    }

    /**
     * Ejecuta la operación authorization definida por la clase ServicioCupones.
     *
     * @return ServicioAutorizacion Resultado de la operación.
     */
    private function autorizacion(): ServicioAutorizacion
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
        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return Schema::hasColumn('cupones', 'empresa_id') && $this->autorizacion()->activeCompanyId() !== null;
    }

    /**
     * Ejecuta la operación empresaActual definida por la clase ServicioCupones.
     *
     * @return int Resultado de la operación.
     */
    private function empresaActual(): int
    {
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasColumn('cupones', 'empresa_id'), 503,
            'Los cupones están deshabilitados hasta aplicar la estructura de propiedad por empresa.');
        // Guarda en $empresaId el resultado necesario para continuar la operacion.
        $empresaId = $this->autorizacion()->activeCompanyId();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para gestionar cupones.');

        return $empresaId;
    }
}
