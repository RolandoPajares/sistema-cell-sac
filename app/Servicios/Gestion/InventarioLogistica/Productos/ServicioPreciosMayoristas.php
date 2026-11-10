<?php

namespace App\Servicios\Gestion\InventarioLogistica\Productos;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\PrecioMayorista;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Define la responsabilidad de la clase ServicioPreciosMayoristas dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
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

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $archivo  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    public function importarCsv(UploadedFile $archivo, User $usuario, ?string $ip): int
    {
        abort_unless(Schema::hasTable('precios_mayoristas'), 503, 'Los precios mayoristas requieren aplicar primero la estructura SQL aprobada.');
        $autorizacion = app(ServicioAutorizacion::class);
        abort_unless($autorizacion->allowsGlobal($usuario, 'gestion.productos.actualizar'), 403);
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para importar precios mayoristas.');
        $empresa = DB::table('empresas')->where('id', $empresaId)->first(['moneda_base']);
        $handle = fopen($archivo->getRealPath(), 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['archivo' => 'No se pudo leer el archivo CSV.']);
        }

        try {
            $headers = fgetcsv($handle);
            $headers = array_map(static fn ($header): string => mb_strtolower(trim((string) $header), 'UTF-8'), $headers ?: []);
            if (isset($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]) ?? $headers[0];
            }
            $esperados = ['sku', 'canal', 'precio', 'moneda', 'vigencia_desde', 'vigencia_hasta', 'motivo'];
            if ($headers !== $esperados) {
                throw ValidationException::withMessages(['archivo' => 'La cabecera debe ser: '.implode(',', $esperados).'.']);
            }
            $filas = [];
            $skus = [];
            while (($fila = fgetcsv($handle)) !== false) {
                if (count($fila) === 1 && trim((string) $fila[0]) === '') {
                    continue;
                }
                if (count($fila) !== count($esperados)) {
                    throw ValidationException::withMessages(['archivo' => 'La fila '.(count($filas) + 2).' no tiene siete columnas.']);
                }
                $datos = array_combine($esperados, array_map(static fn ($valor): string => trim((string) $valor), $fila));
                $filaNumero = count($filas) + 2;
                $sku = (string) $datos['sku'];
                $precio = filter_var($datos['precio'], FILTER_VALIDATE_FLOAT);
                if ($sku === '' || in_array($sku, $skus, true) || $datos['canal'] !== 'mayorista' || $precio === false || $precio <= 0
                    || ($empresa?->moneda_base !== null && strtoupper($datos['moneda']) !== strtoupper((string) $empresa->moneda_base))
                    || $datos['motivo'] === '') {
                    throw ValidationException::withMessages(['archivo' => 'La fila '.$filaNumero.' contiene un SKU, canal, precio, moneda o motivo inválido.']);
                }
                try {
                    CarbonImmutable::parse($datos['vigencia_desde']);
                    if ($datos['vigencia_hasta'] !== '') {
                        CarbonImmutable::parse($datos['vigencia_hasta']);
                    }
                } catch (\Throwable) {
                    throw ValidationException::withMessages(['archivo' => 'Las fechas de la fila '.$filaNumero.' no son válidas.']);
                }
                $filas[] = $datos;
                $skus[] = $sku;
                if (count($filas) > 500) {
                    throw ValidationException::withMessages(['archivo' => 'El archivo no puede superar 500 filas.']);
                }
            }
        } finally {
            fclose($handle);
        }
        if ($filas === []) {
            throw ValidationException::withMessages(['archivo' => 'El archivo no contiene filas para importar.']);
        }

        return DB::transaction(function () use ($filas, $empresaId, $usuario, $ip): int {
            foreach ($filas as $indice => $fila) {
                $productoId = DB::table('variantes_productos as variante')
                    ->join('productos', 'productos.id', '=', 'variante.producto_id')
                    ->where('productos.empresa_id', $empresaId)
                    ->where('variante.codigo_sku', $fila['sku'])
                    ->value('productos.id');
                if ($productoId === null) {
                    throw ValidationException::withMessages(['archivo' => 'No existe un producto de la empresa autorizada para el SKU '.$fila['sku'].' (fila '.($indice + 2).').']);
                }
                $this->registrar([
                    'producto_id' => (int) $productoId,
                    'precio' => $fila['precio'],
                    'vigente_desde' => $fila['vigencia_desde'],
                    'vigente_hasta' => $fila['vigencia_hasta'] !== '' ? $fila['vigencia_hasta'] : null,
                    'motivo' => $fila['motivo'],
                ], $usuario, $ip);
            }

            return count($filas);
        }, 3);
    }

    /**
     * Ejecuta la operación precioVigente definida por la clase ServicioPreciosMayoristas.
     *
     * @param  mixed  $empresaId  Parámetro recibido por la operación.
     * @param  mixed  $productoId  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
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
