<?php

// Devuelve la configuracion que describe este modulo y sus elementos visuales.
return [
    'key' => 'configuracion-general',
    'area' => 'Configuración',
    'title' => 'Configuración general',
    'description' => 'Centraliza preferencias generales del panel sin alterar la configuración actual del sistema.',
    'icon' => 'bi-gear',
    'record_singular' => 'preferencia',
    'record_plural' => 'preferencias',
    'mode' => 'consult',
    // Define las tarjetas de indicadores que resumen el estado del modulo.
    'kpis' => [
        ['label' => 'Preferencias', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-sliders', 'tone' => 'blue'],
        ['label' => 'Idioma', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-translate', 'tone' => 'green'],
        ['label' => 'Zona horaria', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-clock', 'tone' => 'purple'],
        ['label' => 'Estado', 'value' => '—', 'helper' => 'Sin datos integrados', 'icon' => 'bi-check2-circle', 'tone' => 'orange'],
    ],
    // Define los graficos y datos resumidos que se muestran en el analisis del modulo.
    'analytics' => [
        ['title' => 'Preferencias generales', 'icon' => 'bi-gear', 'description' => 'Estado de las preferencias del panel.', 'type' => 'line', 'labels' => ['Idioma', 'Zona horaria', 'Avisos', 'Vista', 'Sistema']],
        ['title' => 'Configuración por área', 'icon' => 'bi-pie-chart', 'description' => 'Distribución de preferencias disponibles.', 'type' => 'donut', 'legend' => ['Panel', 'Idioma', 'Avisos', 'Sistema']],
    ],
    // Define los encabezados de la tabla principal.
    'columns' => ['Preferencia', 'Valor actual', 'Ámbito', 'Estado'],
];
