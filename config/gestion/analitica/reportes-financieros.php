<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'reportes-financieros',
    'area' => 'Análisis y reportes',
    'title' => 'Reportes financieros',
    'description' => 'Prepara consultas financieras para ingresos, gastos, reembolsos y resultados.',
    'icon' => 'bi-graph-up-arrow',
    'record_singular' => 'reporte',
    'record_plural' => 'reportes',
    'mode' => 'consult',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Ingresos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-cash-stack', 'tone' => 'green'],
        ['label' => 'Gastos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-wallet2', 'tone' => 'orange'],
        ['label' => 'Reembolsos', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-arrow-repeat', 'tone' => 'purple'],
        ['label' => 'Resultado', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-bar-chart-line', 'tone' => 'blue'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Ingresos y gastos', 'icon' => 'bi-bar-chart-line', 'description' => 'Comparación financiera del periodo.', 'type' => 'line', 'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May']],
        ['title' => 'Resultado financiero', 'icon' => 'bi-pie-chart', 'description' => 'Composición del resultado disponible.', 'type' => 'donut', 'legend' => ['Ingresos', 'Gastos', 'Reembolsos', 'Resultado']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Reporte', 'Periodo', 'Ingresos', 'Gastos', 'Resultado', 'Estado'],
];
