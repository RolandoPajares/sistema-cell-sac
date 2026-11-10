<?php

namespace App\Servicios\Gestion\InventarioLogistica\Productos;

use App\Models\Comercio\ConfiguracionTienda;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioConfiguracionTienda dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioConfiguracionTienda
{
    private const CAMPOS_POR_SECCION = [
        'general' => ['nombre_tienda', 'dominio', 'correo', 'telefono', 'descripcion'],
        'apariencia' => ['color_principal', 'estilo_portada'],
        'pagos' => ['tipo_precios', 'medios_pago'],
        'envios' => ['costo_estandar', 'recojo_tienda', 'plazo_estimado'],
        'notificaciones' => ['confirmacion_pedido', 'cambio_estado', 'correo_remitente'],
        'seo' => ['titulo', 'descripcion', 'analitica_id', 'pixel_id'],
    ];

    /** @param array{seccion:string,configuracion:array<string,mixed>} $datos */
    public function guardar(array $datos, User $usuario, ?string $ip): ConfiguracionTienda
    {
        abort_unless(Schema::hasTable('configuraciones_tienda'), 503,
            'La configuración persistente requiere aplicar primero la estructura SQL aprobada.');
        $autorizacion = app(ServicioAutorizacion::class);
        abort_unless($autorizacion->allowsGlobal($usuario, 'gestion.configuracion.actualizar'), 403);
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para guardar la configuración de tienda.');

        $seccion = $datos['seccion'];
        $configuracionSeccion = $datos['configuracion'];
        abort_unless(isset(self::CAMPOS_POR_SECCION[$seccion]), 422, 'La sección de configuración no es válida.');
        $camposPermitidos = array_flip(self::CAMPOS_POR_SECCION[$seccion]);
        abort_if(array_diff_key($configuracionSeccion, $camposPermitidos) !== [], 422,
            'La sección contiene opciones que no se pueden guardar.');

        if ($seccion === 'pagos' && isset($configuracionSeccion['medios_pago'])) {
            $mediosActivos = DB::table('medios_pago')->where('activo', true)->whereIn('id', $configuracionSeccion['medios_pago'])->count();
            abort_unless($mediosActivos === count($configuracionSeccion['medios_pago']), 422,
                'Selecciona únicamente medios de pago activos registrados.');
        }

        return DB::transaction(function () use ($seccion, $configuracionSeccion, $usuario, $ip, $empresaId): ConfiguracionTienda {
            $registro = ConfiguracionTienda::query()->where('empresa_id', $empresaId)->lockForUpdate()->first();
            $configuracionAnterior = $registro?->configuracion_json ?? [];
            $configuracionNueva = array_replace($configuracionAnterior, [$seccion => $configuracionSeccion]);
            $valoresAnteriores = $registro === null ? null : $registro->configuracion_json;

            if ($registro === null) {
                $registro = ConfiguracionTienda::query()->create([
                    'empresa_id' => $empresaId,
                    'configuracion_json' => $configuracionNueva,
                    'actualizado_por_usuario_id' => $usuario->getKey(),
                ]);
            } else {
                $registro->update([
                    'configuracion_json' => $configuracionNueva,
                    'actualizado_por_usuario_id' => $usuario->getKey(),
                ]);
            }

            app(RegistradorAuditoria::class)->record(
                (int) $usuario->getKey(), 'configuracion_tienda.actualizada', 'configuraciones_tienda', $registro->getKey(),
                $valoresAnteriores, $configuracionNueva, $ip, scope: ['empresa_id' => $empresaId],
            );

            return $registro->refresh();
        }, 3);
    }

    /** @return array<string, mixed> */
    public function consultar(int $empresaId): array
    {
        if (! Schema::hasTable('configuraciones_tienda')) {
            return [];
        }

        return ConfiguracionTienda::query()->where('empresa_id', $empresaId)->value('configuracion_json') ?? [];
    }
}
