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
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(Schema::hasTable('configuraciones_tienda'), 503,
            'La configuración persistente requiere aplicar primero la estructura SQL aprobada.');
        // Guarda en $autorizacion el resultado necesario para continuar la operacion.
        $autorizacion = app(ServicioAutorizacion::class);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($autorizacion->allowsGlobal($usuario, 'gestion.configuracion.actualizar'), 403);
        // Guarda en $empresaId el resultado necesario para continuar la operacion.
        $empresaId = $autorizacion->activeCompanyId();
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para guardar la configuración de tienda.');

        // Guarda en $seccion el resultado necesario para continuar la operacion.
        $seccion = $datos['seccion'];
        // Guarda en $configuracionSeccion el resultado necesario para continuar la operacion.
        $configuracionSeccion = $datos['configuracion'];
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_unless(isset(self::CAMPOS_POR_SECCION[$seccion]), 422, 'La sección de configuración no es válida.');
        // Guarda en $camposPermitidos el resultado necesario para continuar la operacion.
        $camposPermitidos = array_flip(self::CAMPOS_POR_SECCION[$seccion]);
        // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
        abort_if(array_diff_key($configuracionSeccion, $camposPermitidos) !== [], 422,
            'La sección contiene opciones que no se pueden guardar.');

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($seccion === 'pagos' && isset($configuracionSeccion['medios_pago'])) {
            // Prepara en $mediosActivos la consulta que obtiene o modifica los datos necesarios.
            // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
            $mediosActivos = DB::table('medios_pago')->where('activo', true)->whereIn('id', $configuracionSeccion['medios_pago'])->count();
            // Detiene la operacion cuando la condicion de seguridad o negocio no se cumple.
            abort_unless($mediosActivos === count($configuracionSeccion['medios_pago']), 422,
                'Selecciona únicamente medios de pago activos registrados.');
        }

        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        return DB::transaction(function () use ($seccion, $configuracionSeccion, $usuario, $ip, $empresaId): ConfiguracionTienda {
            // Prepara en $registro la consulta que obtiene o modifica los datos necesarios.
            $registro = ConfiguracionTienda::query()->where('empresa_id', $empresaId)->lockForUpdate()->first();
            // Guarda en $configuracionAnterior el resultado necesario para continuar la operacion.
            $configuracionAnterior = $registro?->configuracion_json ?? [];
            $configuracionNueva = array_replace($configuracionAnterior, [$seccion => $configuracionSeccion]);
            $valoresAnteriores = $registro === null ? null : $registro->configuracion_json;

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($registro === null) {
                // Prepara en $registro la consulta que obtiene o modifica los datos necesarios.
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
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! Schema::hasTable('configuraciones_tienda')) {
            return [];
        }

        // Devuelve el resultado obtenido mediante el modelo correspondiente.
        return ConfiguracionTienda::query()->where('empresa_id', $empresaId)->value('configuracion_json') ?? [];
    }
}
