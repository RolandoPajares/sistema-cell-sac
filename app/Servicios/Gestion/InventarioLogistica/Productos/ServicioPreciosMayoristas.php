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
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasTable('precios_mayoristas'), 503,
            'Los precios mayoristas requieren aplicar primero la estructura SQL aprobada.');
        // Guarda en $autorizacion el resultado necesario para continuar la operacion.
        $autorizacion = app(ServicioAutorizacion::class);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($autorizacion->allowsGlobal($usuario, 'gestion.productos.actualizar'), 403);
        // Guarda en $empresaId el resultado necesario para continuar la operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para registrar precios mayoristas.');

        // Guarda en $inicio el resultado necesario para continuar la operacion.
        $inicio = CarbonImmutable::parse($datos['vigente_desde'])->startOfDay();
        $fin = isset($datos['vigente_hasta']) && $datos['vigente_hasta'] !== ''
            ? CarbonImmutable::parse($datos['vigente_hasta'])->endOfDay()
            : null;
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if($fin !== null && $fin->lt($inicio), 422, 'La vigencia final no puede ser anterior al inicio.');

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        return DB::transaction(function () use ($datos, $usuario, $ip, $empresaId, $inicio, $fin): PrecioMayorista {
            // Prepara en $producto la consulta que obtiene o modifica los datos necesarios.
            $producto = Producto::query()->whereKey($datos['producto_id'])->where('empresa_id', $empresaId)->lockForUpdate()->firstOrFail();
            // Prepara en $preciosEnConflicto la consulta que obtiene o modifica los datos necesarios.
            $preciosEnConflicto = PrecioMayorista::query()->where('empresa_id', $empresaId)
                ->where('producto_id', $producto->getKey())->whereNull('variante_id')
                ->where('vigente_desde', '<=', $fin ?? '9999-12-31 23:59:59.999999')
                ->where(fn ($consulta) => $consulta->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', $inicio))
                ->orderBy('vigente_desde')->lockForUpdate()->get();

            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($preciosEnConflicto as $precioExistente) {
                // Guarda en $inicioExistente el resultado necesario para continuar la operacion.
                $inicioExistente = CarbonImmutable::parse($precioExistente->vigente_desde);
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
                abort_if($inicioExistente->greaterThanOrEqualTo($inicio), 409,
                    'La vigencia se cruza con un precio mayorista futuro. Ajusta las fechas antes de guardar.');
                // Guarda en $finAnterior el resultado necesario para continuar la operacion.
                $finAnterior = $inicio->subMicrosecond();
                // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
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

            // Prepara en $precio la consulta que obtiene o modifica los datos necesarios.
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
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasTable('precios_mayoristas'), 503, 'Los precios mayoristas requieren aplicar primero la estructura SQL aprobada.');
        // Guarda en $autorizacion el resultado necesario para continuar la operacion.
        $autorizacion = app(ServicioAutorizacion::class);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($autorizacion->allowsGlobal($usuario, 'gestion.productos.actualizar'), 403);
        // Guarda en $empresaId el resultado necesario para continuar la operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para importar precios mayoristas.');
        // Prepara en $empresa la consulta que obtiene o modifica los datos necesarios.
        $empresa = DB::table('empresas')->where('id', $empresaId)->first(['moneda_base']);
        // Guarda en $handle el resultado necesario para continuar la operacion.
        $handle = fopen($archivo->getRealPath(), 'rb');
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($handle === false) {
            throw ValidationException::withMessages(['archivo' => 'No se pudo leer el archivo CSV.']);
        }

        try {
            // Guarda en $headers el resultado necesario para continuar la operacion.
            $headers = fgetcsv($handle);
            $headers = array_map(static fn ($header): string => mb_strtolower(trim((string) $header), 'UTF-8'), $headers ?: []);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (isset($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]) ?? $headers[0];
            }
            // Agrupa en $esperados los datos que se utilizaran en el siguiente paso.
            $esperados = ['sku', 'canal', 'precio', 'moneda', 'vigencia_desde', 'vigencia_hasta', 'motivo'];
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($headers !== $esperados) {
                throw ValidationException::withMessages(['archivo' => 'La cabecera debe ser: '.implode(',', $esperados).'.']);
            }
            // Agrupa en $filas los datos que se utilizaran en el siguiente paso.
            $filas = [];
            // Agrupa en $skus los datos que se utilizaran en el siguiente paso.
            $skus = [];
            while (($fila = fgetcsv($handle)) !== false) {
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if (count($fila) === 1 && trim((string) $fila[0]) === '') {
                    continue;
                }
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if (count($fila) !== count($esperados)) {
                    throw ValidationException::withMessages(['archivo' => 'La fila '.(count($filas) + 2).' no tiene siete columnas.']);
                }
                $datos = array_combine($esperados, array_map(static fn ($valor): string => trim((string) $valor), $fila));
                // Guarda en $filaNumero el resultado necesario para continuar la operacion.
                $filaNumero = count($filas) + 2;
                // Guarda en $sku el resultado necesario para continuar la operacion.
                $sku = (string) $datos['sku'];
                // Guarda en $precio el resultado necesario para continuar la operacion.
                $precio = filter_var($datos['precio'], FILTER_VALIDATE_FLOAT);
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($sku === '' || in_array($sku, $skus, true) || $datos['canal'] !== 'mayorista' || $precio === false || $precio <= 0
                    || ($empresa?->moneda_base !== null && strtoupper($datos['moneda']) !== strtoupper((string) $empresa->moneda_base))
                    || $datos['motivo'] === '') {
                    throw ValidationException::withMessages(['archivo' => 'La fila '.$filaNumero.' contiene un SKU, canal, precio, moneda o motivo inválido.']);
                }
                try {
                    CarbonImmutable::parse($datos['vigencia_desde']);
                    // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                    if ($datos['vigencia_hasta'] !== '') {
                        CarbonImmutable::parse($datos['vigencia_hasta']);
                    }
                } catch (\Throwable) {
                    throw ValidationException::withMessages(['archivo' => 'Las fechas de la fila '.$filaNumero.' no son válidas.']);
                }
                $filas[] = $datos;
                $skus[] = $sku;
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if (count($filas) > 500) {
                    throw ValidationException::withMessages(['archivo' => 'El archivo no puede superar 500 filas.']);
                }
            }
        } finally {
            fclose($handle);
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($filas === []) {
            throw ValidationException::withMessages(['archivo' => 'El archivo no contiene filas para importar.']);
        }

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($filas, $empresaId, $usuario, $ip): int {
            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($filas as $indice => $fila) {
                // Prepara en $productoId la consulta que obtiene o modifica los datos necesarios.
                $productoId = DB::table('variantes_productos as variante')
                    ->join('productos', 'productos.id', '=', 'variante.producto_id')
                    ->where('productos.empresa_id', $empresaId)
                    ->where('variante.codigo_sku', $fila['sku'])
                    ->value('productos.id');
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($productoId === null) {
                    throw ValidationException::withMessages(['archivo' => 'No existe un producto de la empresa autorizada para el SKU '.$fila['sku'].' (fila '.($indice + 2).').']);
                }
                // Delega esta parte de la operacion al servicio o dependencia correspondiente.
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
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasTable('precios_mayoristas')) {
            return null;
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
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
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasTable('precios_mayoristas') || $productosIds === []) {
            return collect();
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return PrecioMayorista::query()->where('empresa_id', $empresaId)->whereIn('producto_id', $productosIds)
            ->whereNull('variante_id')->where('vigente_desde', '<=', now())
            ->where(fn ($consulta) => $consulta->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', now()))
            ->orderByDesc('vigente_desde')->orderByDesc('id')->get()->unique('producto_id')->keyBy('producto_id');
    }
}
