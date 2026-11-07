<?php

return [
    'key' => 'reportes-financieros',
    'area' => 'Análisis y reportes',
    'title' => 'Reportes financieros',
    'description' => 'Prepara consultas financieras para ingresos, gastos, reembolsos y resultados.',
    'icon' => 'bi-graph-up-arrow',
    'record_singular' => 'reporte',
    'record_plural' => 'reportes',
    'mode' => 'consult',
    'kpis' => [
        ['label' => 'Ingresos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-cash-stack', 'tone' => 'green'],
        ['label' => 'Gastos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-wallet2', 'tone' => 'orange'],
        ['label' => 'Reembolsos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-arrow-repeat', 'tone' => 'purple'],
        ['label' => 'Resultado', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-bar-chart-line', 'tone' => 'blue'],
    ],
    'analytics' => [
        ['title' => 'Ingresos y gastos', 'icon' => 'bi-bar-chart-line', 'description' => 'Comparación financiera del periodo.', 'type' => 'line', 'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May']],
        ['title' => 'Resultado financiero', 'icon' => 'bi-pie-chart', 'description' => 'Composición del resultado disponible.', 'type' => 'donut', 'legend' => ['Ingresos', 'Gastos', 'Reembolsos', 'Resultado']],
    ],
    'columns' => ['Reporte', 'Periodo', 'Ingresos', 'Gastos', 'Resultado', 'Estado'],
];
