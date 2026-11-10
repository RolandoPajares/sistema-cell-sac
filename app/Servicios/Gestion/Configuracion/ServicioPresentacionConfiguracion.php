<?php

namespace App\Servicios\Gestion\Configuracion;

use Illuminate\Support\Facades\Route;

/**
 * Prepara los datos de presentación compartidos por las pantallas de configuración.
 */
final class ServicioPresentacionConfiguracion
{
    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function preparar(string $tipo, array $datos): array
    {
        $paginas = [
            'general' => [
                'titulo' => 'Configuración general',
                'icono' => 'bi-gear',
                'descripcion' => 'Consulta los datos empresariales y parámetros persistidos disponibles.',
                'acciones' => [['texto' => 'Editar datos empresariales', 'icono' => 'bi-building', 'tipo' => 'empresa']],
            ],
            'integraciones' => [
                'titulo' => 'Integraciones',
                'icono' => 'bi-plug',
                'descripcion' => 'Consulta las integraciones y sus eventos registrados para la empresa autorizada.',
                'acciones' => [['texto' => 'Nueva integración', 'icono' => 'bi-plus-lg', 'tipo' => 'nueva']],
            ],
            'notificaciones' => [
                'titulo' => 'Notificaciones',
                'icono' => 'bi-bell',
                'descripcion' => 'Consulta las notificaciones persistidas del usuario autenticado.',
                'acciones' => [],
            ],
            'seguridad' => [
                'titulo' => 'Configuración de seguridad',
                'icono' => 'bi-shield-check',
                'descripcion' => 'Consulta sesiones y eventos de auditoría disponibles en el alcance autorizado.',
                'acciones' => Route::has('management.security_sessions')
                    ? [['texto' => 'Ver sesiones y accesos', 'icono' => 'bi-shield-check', 'tipo' => 'sesiones']]
                    : [],
            ],
            'respaldos' => [
                'titulo' => 'Respaldos y recuperación',
                'icono' => 'bi-database-gear',
                'descripcion' => 'Genera y descarga respaldos verificables de la base autorizada.',
                'acciones' => [['texto' => 'Crear respaldo', 'icono' => 'bi-plus-lg', 'tipo' => 'crear']],
            ],
        ];

        $metricas = match ($tipo) {
            'general' => [
                ['etiqueta' => 'Sedes activas', 'valor' => $datos['sedes']->count(), 'icono' => 'bi-shop', 'tono' => 'azul', 'ayuda' => 'Empresa autorizada'],
                ['etiqueta' => 'Usuarios vinculados a sede', 'valor' => $datos['usuariosVinculados'], 'icono' => 'bi-people', 'tono' => 'azul', 'ayuda' => 'Asignaciones vigentes'],
                ['etiqueta' => 'Integraciones activas', 'valor' => $datos['integracionesActivas'], 'icono' => 'bi-layers', 'tono' => 'verde', 'ayuda' => 'MariaDB'],
                ['etiqueta' => 'Respaldos registrados', 'valor' => 0, 'icono' => 'bi-database', 'tono' => 'violeta', 'ayuda' => 'Sin tabla de historial'],
            ],
            'integraciones' => [
                ['etiqueta' => 'Integraciones totales', 'valor' => $datos['totales']['total'], 'icono' => 'bi-link-45deg', 'tono' => 'azul', 'ayuda' => 'MariaDB'],
                ['etiqueta' => 'Activas', 'valor' => $datos['totales']['activas'], 'icono' => 'bi-check-circle', 'tono' => 'verde', 'ayuda' => 'Estado persistido'],
                ['etiqueta' => 'Inactivas', 'valor' => $datos['totales']['inactivas'], 'icono' => 'bi-exclamation-triangle', 'tono' => 'ambar', 'ayuda' => 'Estado persistido'],
                ['etiqueta' => 'Eventos recientes', 'valor' => $datos['totales']['eventos'], 'icono' => 'bi-clock', 'tono' => 'gris', 'ayuda' => 'Últimos eventos'],
            ],
            'notificaciones' => [
                ['etiqueta' => 'Notificaciones', 'valor' => $datos['resumen']['total'], 'icono' => 'bi-send', 'tono' => 'azul', 'ayuda' => 'Del usuario actual'],
                ['etiqueta' => 'No leídas', 'valor' => $datos['resumen']['no_leidas'], 'icono' => 'bi-clock', 'tono' => 'ambar', 'ayuda' => 'MariaDB'],
                ['etiqueta' => 'Leídas', 'valor' => $datos['resumen']['leidas'], 'icono' => 'bi-check-circle', 'tono' => 'verde', 'ayuda' => 'MariaDB'],
                ['etiqueta' => 'Tipos registrados', 'valor' => $datos['resumen']['tipos'], 'icono' => 'bi-layers', 'tono' => 'violeta', 'ayuda' => 'MariaDB'],
            ],
            'seguridad' => [
                ['etiqueta' => 'Sesiones registradas', 'valor' => $datos['sesiones']->count(), 'icono' => 'bi-people', 'tono' => 'azul', 'ayuda' => 'Usuario actual'],
                ['etiqueta' => 'Sesiones vigentes', 'valor' => $datos['sesiones']->whereNull('revocada_en')->count(), 'icono' => 'bi-check-circle', 'tono' => 'verde', 'ayuda' => 'Tabla de sesiones'],
                ['etiqueta' => 'Eventos de seguridad', 'valor' => $datos['eventos']->count(), 'icono' => 'bi-shield-x', 'tono' => 'rojo', 'ayuda' => 'Auditoría autorizada'],
                ['etiqueta' => 'Políticas persistidas', 'valor' => 0, 'icono' => 'bi-layers', 'tono' => 'gris', 'ayuda' => 'Sin tabla de políticas'],
            ],
            default => [
                ['etiqueta' => 'Respaldos registrados', 'valor' => count($datos['respaldos']), 'icono' => 'bi-database', 'tono' => 'azul', 'ayuda' => 'Archivos locales'],
                ['etiqueta' => 'Último respaldo', 'valor' => $datos['respaldos'][0]['nombre'] ?? '—', 'icono' => 'bi-calendar-check', 'tono' => 'verde', 'ayuda' => 'Sincronizado localmente'],
                ['etiqueta' => 'Almacenamiento usado', 'valor' => $this->tamanoRespaldos($datos['respaldos']), 'icono' => 'bi-hdd', 'tono' => 'azul', 'ayuda' => 'Archivos generados'],
                ['etiqueta' => 'Puntos de recuperación', 'valor' => count($datos['respaldos']), 'icono' => 'bi-shield-check', 'tono' => 'violeta', 'ayuda' => 'Historial local'],
            ],
        };

        return $datos + [
            'pagina' => $paginas[$tipo],
            'metricas' => $metricas,
            'tipo' => $tipo,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $respaldos
     */
    private function tamanoRespaldos(array $respaldos): string
    {
        $bytes = array_sum(array_map(static fn (array $respaldo): int => (int) ($respaldo['tamano'] ?? 0), $respaldos));

        return number_format($bytes / 1048576, 2).' MB';
    }
}
