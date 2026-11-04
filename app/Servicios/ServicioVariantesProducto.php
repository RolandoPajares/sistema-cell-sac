<?php

namespace App\Servicios;

use App\Models\Catalogo\ImagenVariante;
use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ServicioVariantesProducto
{
    public function __construct(private readonly RegistradorAuditoria $auditoria) {}

    /**
     * @param  array{codigo_sku:?string,codigo_barras:?string,nombre_color:string,codigo_color:?string}  $datos
     */
    public function registrar(Producto $producto, array $datos, User $usuario, ?string $ip): VarianteProducto
    {
        return DB::transaction(function () use ($producto, $datos, $usuario, $ip): VarianteProducto {
            $productoBloqueado = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            $saldoBasePendiente = DB::table('existencias_almacen')
                ->where('producto_id', $productoBloqueado->id)
                ->where('empresa_id', $productoBloqueado->empresa_id)
                ->whereNull('variante_id')
                ->where(function ($consulta): void {
                    $consulta->where('cantidad_fisica', '>', 0)->orWhere('cantidad_reservada', '>', 0);
                })
                ->exists();

            abort_if($saldoBasePendiente, 409, 'El producto tiene existencias sin variante asignada. Ajusta ese saldo desde Inventario antes de registrar variantes.');

            $variante = $productoBloqueado->variants()->create($datos + [
                'estado' => 'activo',
                'fecha_creacion' => now(),
            ]);

            $this->auditoria->record(
                $usuario->id,
                'variant.created',
                'variantes_productos',
                $variante->id,
                null,
                $variante->only(['producto_id', 'codigo_sku', 'codigo_barras', 'nombre_color', 'codigo_color', 'estado']),
                $ip,
                scope: ['empresa_id' => (int) $productoBloqueado->empresa_id],
            );

            return $variante;
        }, 3);
    }

    /**
     * @param  array{codigo_sku:?string,codigo_barras:?string,nombre_color:string,codigo_color:?string,estado?:string}  $datos
     */
    public function actualizar(Producto $producto, VarianteProducto $variante, array $datos, User $usuario, ?string $ip): VarianteProducto
    {
        return DB::transaction(function () use ($producto, $variante, $datos, $usuario, $ip): VarianteProducto {
            $productoBloqueado = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            $varianteBloqueada = $productoBloqueado->variants()
                ->whereKey($variante->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $antes = $varianteBloqueada->only(['codigo_sku', 'codigo_barras', 'nombre_color', 'codigo_color', 'estado']);
            $varianteBloqueada->update($datos);

            $this->auditoria->record(
                $usuario->id,
                'variant.updated',
                'variantes_productos',
                $varianteBloqueada->id,
                $antes,
                $varianteBloqueada->only(array_keys($antes)),
                $ip,
                scope: ['empresa_id' => (int) $productoBloqueado->empresa_id],
            );

            return $varianteBloqueada;
        }, 3);
    }

    public function desactivar(Producto $producto, VarianteProducto $variante, User $usuario, ?string $ip): VarianteProducto
    {
        return DB::transaction(function () use ($producto, $variante, $usuario, $ip): VarianteProducto {
            $productoBloqueado = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            $varianteBloqueada = $productoBloqueado->variants()
                ->whereKey($variante->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($varianteBloqueada->estado === 'activo') {
                $varianteBloqueada->update(['estado' => 'inactivo']);
                $this->auditoria->record(
                    $usuario->id,
                    'variant.deactivated',
                    'variantes_productos',
                    $varianteBloqueada->id,
                    ['estado' => 'activo'],
                    ['estado' => 'inactivo'],
                    $ip,
                    scope: ['empresa_id' => (int) $productoBloqueado->empresa_id],
                );
            }

            return $varianteBloqueada;
        }, 3);
    }

    public function registrarImagen(
        Producto $producto,
        VarianteProducto $variante,
        UploadedFile $archivo,
        bool $imagenPrincipal,
        User $usuario,
        ?string $ip,
    ): ImagenVariante {
        $rutaGuardada = $archivo->store('variantes', 'public');
        abort_if($rutaGuardada === false, 500, 'No se pudo guardar la imagen de la variante.');

        try {
            return DB::transaction(function () use ($producto, $variante, $rutaGuardada, $imagenPrincipal, $usuario, $ip): ImagenVariante {
                $productoBloqueado = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
                $varianteBloqueada = $productoBloqueado->variants()
                    ->whereKey($variante->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $imagenPrincipalExistente = $varianteBloqueada->images()
                    ->where('imagen_principal', true)
                    ->orderBy('orden')
                    ->orderBy('id')
                    ->first();
                $esPrincipal = $imagenPrincipal || $imagenPrincipalExistente === null;

                $varianteBloqueada->images()->update(['imagen_principal' => false]);

                if (! $esPrincipal) {
                    $varianteBloqueada->images()
                        ->whereKey($imagenPrincipalExistente->getKey())
                        ->update(['imagen_principal' => true]);
                }

                $ultimoOrden = $varianteBloqueada->images()->max('orden');
                $imagenRegistrada = $varianteBloqueada->images()->create([
                    'ruta_imagen' => 'storage/'.$rutaGuardada,
                    'imagen_principal' => $esPrincipal,
                    'orden' => $ultimoOrden === null ? 0 : (int) $ultimoOrden + 1,
                    'fecha_creacion' => now(),
                ]);

                $this->auditoria->record(
                    $usuario->id,
                    'variante.imagen.registrada',
                    'imagenes_variantes',
                    $imagenRegistrada->id,
                    null,
                    $imagenRegistrada->only(['variante_id', 'ruta_imagen', 'imagen_principal', 'orden']),
                    $ip,
                    scope: ['empresa_id' => (int) $productoBloqueado->empresa_id],
                );

                return $imagenRegistrada;
            }, 3);
        } catch (Throwable $excepcion) {
            Storage::disk('public')->delete($rutaGuardada);

            throw $excepcion;
        }
    }

    public function eliminarImagen(
        Producto $producto,
        VarianteProducto $variante,
        int $imagenId,
        User $usuario,
        ?string $ip,
    ): void {
        $rutaImagenEliminada = DB::transaction(function () use ($producto, $variante, $imagenId, $usuario, $ip): string {
            $productoBloqueado = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            $varianteBloqueada = $productoBloqueado->variants()
                ->whereKey($variante->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $imagen = $varianteBloqueada->images()->whereKey($imagenId)->lockForUpdate()->firstOrFail();
            $eraPrincipal = (bool) $imagen->imagen_principal;
            $rutaImagen = (string) $imagen->ruta_imagen;
            $valoresAnteriores = $imagen->only(['variante_id', 'ruta_imagen', 'imagen_principal', 'orden']);

            $imagen->delete();

            if ($eraPrincipal) {
                $siguienteImagen = $varianteBloqueada->images()
                    ->orderBy('orden')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if ($siguienteImagen !== null) {
                    $varianteBloqueada->images()->update(['imagen_principal' => false]);
                }

                if ($siguienteImagen !== null) {
                    $varianteBloqueada->images()
                        ->whereKey($siguienteImagen->getKey())
                        ->update(['imagen_principal' => true]);
                }
            }

            $this->auditoria->record(
                $usuario->id,
                'variante.imagen.eliminada',
                'imagenes_variantes',
                $imagenId,
                $valoresAnteriores,
                null,
                $ip,
                scope: ['empresa_id' => (int) $productoBloqueado->empresa_id],
            );

            return $rutaImagen;
        }, 3);

        $this->eliminarArchivoSiNoEstaReferenciado($rutaImagenEliminada);
    }

    private function eliminarArchivoSiNoEstaReferenciado(string $rutaImagen): void
    {
        $referenciadaEnVariantes = DB::table('imagenes_variantes')->where('ruta_imagen', $rutaImagen)->exists();
        $referenciadaEnProductos = DB::table('referencias_imagenes_productos')->where('ruta_imagen', $rutaImagen)->exists();
        $utilizadaComoPortada = DB::table('productos')->where('url_imagen', $rutaImagen)->exists();

        if ($referenciadaEnVariantes || $referenciadaEnProductos || $utilizadaComoPortada || ! str_starts_with($rutaImagen, 'storage/')) {
            return;
        }

        $rutaRelativa = substr($rutaImagen, strlen('storage/'));
        $segmentos = explode('/', str_replace('\\', '/', $rutaRelativa));

        if ($rutaRelativa === '' || in_array('..', $segmentos, true) || in_array('.', $segmentos, true)) {
            return;
        }

        Storage::disk('public')->delete($rutaRelativa);
    }
}
