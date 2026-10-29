<?php

return [
    'key' => 'respaldos-mantenimiento',
    'area' => 'Administración del sistema',
    'title' => 'Respaldos y mantenimiento',
    'description' => 'Prepara el panel de control para respaldos, revisiones y tareas de mantenimiento.',
    'icon' => 'bi-database-check',
    'record_singular' => 'tarea',
    'record_plural' => 'tareas',
    'mode' => 'consult',
    'kpis' => [
        ['label' => 'Último respaldo', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-database-check', 'tone' => 'green'],
        ['label' => 'Estado del sistema', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-heart-pulse', 'tone' => 'blue'],
        ['label' => 'Mantenimientos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-tools', 'tone' => 'orange'],
        ['label' => 'Revisiones', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-clipboard-check', 'tone' => 'purple'],
    ],
    'analytics' => [
        ['title' => 'Historial de mantenimiento', 'icon' => 'bi-tools', 'description' => 'Tareas preventivas por periodo.', 'type' => 'line', 'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May']],
        ['title' => 'Estado de respaldos', 'icon' => 'bi-pie-chart', 'description' => 'Resumen de disponibilidad de respaldos.', 'type' => 'donut', 'legend' => ['Correctos', 'Pendientes', 'Revisar', 'No disponible']],
    ],
    'columns' => ['Tarea', 'Última ejecución', 'Responsable', 'Estado', 'Próxima acción'],
];
