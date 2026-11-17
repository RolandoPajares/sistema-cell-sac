<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'respaldos-mantenimiento',
    'area' => 'Administración del sistema',
    'title' => 'Respaldos y mantenimiento',
    'description' => 'Prepara el panel de control para respaldos, revisiones y tareas de mantenimiento.',
    'icon' => 'bi-database-check',
    'record_singular' => 'tarea',
    'record_plural' => 'tareas',
    'mode' => 'consult',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Último respaldo', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-database-check', 'tone' => 'green'],
        ['label' => 'Estado del sistema', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-heart-pulse', 'tone' => 'blue'],
        ['label' => 'Mantenimientos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-tools', 'tone' => 'orange'],
        ['label' => 'Revisiones', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-clipboard-check', 'tone' => 'purple'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Historial de mantenimiento', 'icon' => 'bi-tools', 'description' => 'Tareas preventivas por periodo.', 'type' => 'line', 'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May']],
        ['title' => 'Estado de respaldos', 'icon' => 'bi-pie-chart', 'description' => 'Resumen de disponibilidad de respaldos.', 'type' => 'donut', 'legend' => ['Correctos', 'Pendientes', 'Revisar', 'No disponible']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Tarea', 'Última ejecución', 'Responsable', 'Estado', 'Próxima acción'],
    // Define el contenido informativo y las acciones principales de la pantalla.
    'content' => ['title' => 'Respaldos y mantenimiento', 'description' => 'Prepara copias de seguridad, controla tareas preventivas y revisa la recuperación del sistema.', 'action' => 'Ver bitácora', 'primary' => 'Crear plan de respaldo', 'summary' => ['Planes de respaldo', 'Tareas de mantenimiento', 'Seguridad y recuperación']],
];
