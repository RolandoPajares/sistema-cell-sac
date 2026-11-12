<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'metricas-marketing',
    'area' => 'Análisis y reportes',
    'title' => 'Métricas de marketing',
    'description' => 'Consulta el rendimiento de campañas, publicidad, cupones y promociones.',
    'icon' => 'bi-megaphone',
    'record_singular' => 'métrica',
    'record_plural' => 'métricas',
    'mode' => 'consult',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Vistas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-eye', 'tone' => 'blue'],
        ['label' => 'Clics', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-cursor', 'tone' => 'purple'],
        ['label' => 'Conversión', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-graph-up', 'tone' => 'green'],
        ['label' => 'Campañas activas', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-megaphone', 'tone' => 'orange'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Rendimiento de campañas', 'icon' => 'bi-megaphone', 'description' => 'Vistas, clics y conversión por periodo.', 'type' => 'line', 'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May']],
        ['title' => 'Distribución de resultados', 'icon' => 'bi-pie-chart', 'description' => 'Composición de las métricas disponibles.', 'type' => 'donut', 'legend' => ['Vistas', 'Clics', 'Conversión', 'Campañas']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Campaña', 'Vistas', 'Clics', 'Conversión', 'Periodo', 'Estado'],
];
