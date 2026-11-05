<?php

return [
    'key' => 'analitica-clientes',
    'area' => 'Análisis y reportes',
    'title' => 'Analítica de clientes',
    'description' => 'Organiza indicadores de comportamiento, segmentos y valor de clientes.',
    'icon' => 'bi-people',
    'record_singular' => 'análisis',
    'record_plural' => 'análisis',
    'mode' => 'consult',
    'kpis' => [
        ['label' => 'Clientes analizados', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-people', 'tone' => 'blue'],
        ['label' => 'Conversión', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-funnel', 'tone' => 'green'],
        ['label' => 'Ticket promedio', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-cart-check', 'tone' => 'purple'],
        ['label' => 'Segmentos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-diagram-3', 'tone' => 'orange'],
    ],
    'analytics' => [
        ['title' => 'Evolución de clientes', 'icon' => 'bi-people', 'description' => 'Comportamiento de clientes por periodo.', 'type' => 'line', 'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May']],
        ['title' => 'Distribución por segmento', 'icon' => 'bi-pie-chart', 'description' => 'Composición del público registrado.', 'type' => 'donut', 'legend' => ['Mayoristas', 'Minoristas', 'Nuevos', 'Recurrentes']],
    ],
    'columns' => ['Segmento', 'Clientes', 'Conversión', 'Ticket promedio', 'Periodo', 'Estado'],
];
