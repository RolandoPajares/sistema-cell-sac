<?php

return [
    'key' => 'metricas-marketing',
    'area' => 'Análisis y reportes',
    'title' => 'Métricas de marketing',
    'description' => 'Consulta el rendimiento de campañas, publicidad, cupones y promociones.',
    'icon' => 'bi-megaphone',
    'record_singular' => 'métrica',
    'record_plural' => 'métricas',
    'mode' => 'consult',
    'kpis' => [
        ['label' => 'Vistas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-eye', 'tone' => 'blue'],
        ['label' => 'Clics', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-cursor', 'tone' => 'purple'],
        ['label' => 'Conversión', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-graph-up', 'tone' => 'green'],
        ['label' => 'Campañas activas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-megaphone', 'tone' => 'orange'],
    ],
    'analytics' => [
        ['title' => 'Rendimiento de campañas', 'icon' => 'bi-megaphone', 'description' => 'Vistas, clics y conversión por periodo.', 'type' => 'line', 'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May']],
        ['title' => 'Distribución de resultados', 'icon' => 'bi-pie-chart', 'description' => 'Composición de las métricas disponibles.', 'type' => 'donut', 'legend' => ['Vistas', 'Clics', 'Conversión', 'Campañas']],
    ],
    'columns' => ['Campaña', 'Vistas', 'Clics', 'Conversión', 'Periodo', 'Estado'],
];
